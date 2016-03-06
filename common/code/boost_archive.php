<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost.php');
require_once(dirname(__FILE__) . '/url.php');

define('BOOST_DOCS_MODIFIED_DATE', 'Sat 07 Feb 2015 21:44:00 +0000');

class BoostArchive
{
    var $params;

    function __construct($params = Array()) {
        $this->params = $params;
    }

    function get_archive_location()
    {
        $path_parts = array();
        preg_match($this->params['pattern'], $this->params['vpath'], $path_parts);

        if (in_array($path_parts[1], array('boost-build', 'regression'))) {
            $this->params['version'] = null;
            $version_dir = $path_parts[1];
        } else {
            $this->params['version'] = BoostVersion::from($path_parts[1]);
            $version_dir = is_numeric($path_parts[1][0]) ?
                "boost_{$path_parts[1]}" : $path_parts[1];
        }
        $this->params['key'] = $path_parts[2];

        $file = false;

        if (!$file) {
            $file = $this->params['archive_file_prefix'] . $this->params['key'];
        }

        $this->params['file'] = $file;

        $this->params['archive'] =
                str_replace('\\','/', $this->params['archive_dir'] . '/' . $version_dir . '.zip');
    }

    function display_from_archive($content_map = array())
    {
        // Set default values

        $this->params = array_merge(
            array(
                'pattern' => '@^[/]([^/]+)[/](.*)$@',
                'vpath' => $_SERVER["PATH_INFO"],
                'archive_dir' => ARCHIVE_DIR,
                'archive_file_prefix' => ARCHIVE_FILE_PREFIX,
                'use_http_expire_date' => false,
                'title' => NULL,
                'charset' => NULL,
                'content' => NULL,
                'error' => false,
            ),
            $this->params
        );

        $this->get_archive_location();

        // Only use a permanent redirect for releases (beta or full).

        $redirect_status_code = $this->params['version'] &&
            $this->params['version']->is_numbered_release() ? 301 : 302;

        // Calculate expiry date if requested.

        $expires = null;
        if ($this->params['use_http_expire_date'])
        {
            if (!$this->params['version']) {
                $expires = "+1 week";
            }
            else {
                $compare_version = BoostVersion::from($this->params['version'])->
                    compare(BoostVersion::current());
                $expires = $compare_version === -1 ? "+1 year" :
                    ($compare_version === 0 ? "+1 week" : "+1 day");
            }
        }

        // Check file exists.

        $check_file = $this->params['archive'];

        if (!is_readable($check_file)) {
            error_page($this->params, 'Unable to find zipfile.');
            return;
        }

        // Choose mime type to use
        // TODO: Better way to support mime type? Built in PHP functions
        //       appear to require the actual file, could automatically
        //       grab an updated list from:
        //       http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types

        $mime_types = array(
            'png' => 'image/png',
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'css' => 'text/css',
            'js' => 'application/x-javascript',
            'pdf' => 'application/pdf',
            'html' => 'text/html',
            'htm' => 'text/html',
            'dtd' => 'application/xml-dtd',
            'json' => 'application/json',
        );

        $extension = pathinfo($this->params['key'], PATHINFO_EXTENSION);
        $type = array_key_exists($extension, $mime_types) ? $mime_types[$extension] : 'text/plain';

        // Handle ETags and Last-Modified HTTP headers.

        // Output raw files.

        if (!http_headers($type, filemtime($check_file), $expires))
            return;

        display_raw_file($this->params, $_SERVER['REQUEST_METHOD'], $type);
    }
}

// HTTP header handling

function http_headers($type, $last_modified, $expires = null)
{
    header('Content-type: '.$type);
    switch($type) {
        case 'image/png':
        case 'image/gif':
        case 'image/jpeg':
        case 'text/css':
        case 'application/x-javascript':
        case 'application/pdf':
        case 'application/xml-dtd':
            header('Expires: '.date(DATE_RFC2822, strtotime("+1 year")));
            header('Cache-Control: max-age=31556926'); // A year, give or take a day.
            break;
        default:
            if($expires) {
                header('Expires: '.date(DATE_RFC2822, strtotime($expires)));
                header('Cache-Control: max-age='.strtotime($expires, 0));
            }
            break;
    }
    
    return conditional_get(max(
        strtotime(BOOST_DOCS_MODIFIED_DATE),        // last manual documenation update
        filemtime(dirname(__FILE__).'/boost.php'),  // last release (since the version number is updated)
        $last_modified                              // when the file was modified
    ));
}

function conditional_get($last_modified)
{
    if(!$last_modified) return true;

    $last_modified_text = date(DATE_RFC2822, $last_modified);
    $etag = '"'.md5($last_modified).'"';

    header("Last-Modified: $last_modified_text");
    header("ETag: $etag");

    $checked = false;

    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $checked = true;
        $if_modified_since = strtotime(stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']));
        if(!$if_modified_since || $if_modified_since < $last_modified)
            return true;
    }

    if(isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
        $checked = true;
        if(stripslashes($_SERVER['HTTP_IF_NONE_MATCH'] != $etag))
            return true;
    }
    
    if(!$checked) return true;
    
    header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');
    return false;
}

function display_raw_file($params, $method, $type)
{
    ## header('Content-Disposition: attachment; filename="downloaded.pdf"');
    if ($method == 'HEAD') {
        $output = null;
        exec(unzip_command($params).' > /dev/null', $output, $exit_status);
    } else {
        $file_handle = popen(unzip_command($params), 'rb');
        fpassthru($file_handle);
        $exit_status = pclose($file_handle);
    }

    // Don't display errors for a corrupt zip file, as we seemd to
    // be getting them for legitimate files.

    if($exit_status > 1) {
        unzip_error($params, $exit_status);
        if ($params['error']) {
            header("{$_SERVER["SERVER_PROTOCOL"]} {$params['error']}", true);
        }
        echo "Error extracting file: {$params['content']}";
    }
}

function unzip_command($params) {
    return
      UNZIP
      .' -p '.escapeshellarg($params['archive'])
      .' '.escapeshellarg($params['file']);
}

/* File Not Found */

function error_page($params, $message = null)
{
    if (!$params['error']) { $params['error'] = "404 Not Found"; }
    header("{$_SERVER["SERVER_PROTOCOL"]} {$params['error']}");

    $head = <<<HTML
  <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
  <title>Boost C++ Libraries - 404 Not Found</title>
HTML;

    $content = '<h1>'.html_encode($params['error']).'</h1><p>File "' . html_encode($params['file']) . '" not found.</p><p>';
    $content .= "Unzip error: ";
    $content .= html_encode($message);
    $content .= '</p>';

    $filter = new BoostFilters($params);
    $filter->display_template(Array('head' => $head, 'content' => $content));
}

// Updates $params with the appropriate unzip error.

function unzip_error(&$params, $exit_status) {
    $code="500 Internal Server Error";

    switch($exit_status) {
    case 0: $message = 'No error.'; $code = null; break;
    case 1: $message = 'One  or  more  warning  errors  were  encountered.'; break;
    case 2: $message = 'A generic error in the zipfile format was detected.'; break;
    case 3: $message = 'A severe error in the zipfile format was detected.'; break;
    case 4: $message = 'Unzip was unable to allocate memory for one or more buffers during program initialization.'; break;
    case 5: $message = 'Unzip was unable to allocate memory or unable to obtain a tty to read the decryption password(s).'; break;
    case 6: $message = 'Unzip was unable to allocate memory during decompression to disk.'; break;
    case 7: $message = 'Unzip was unable to allocate memory during in-memory decompression.'; break;
    case 9:
        $message = 'The specified zipfile was not found';
        if (isset($params['archive']) && is_file($params['archive'])) {
            $message .= ' <i>(the file exists, so this is probably an error reading the file)</i>';
        }
        $message .= '.';
        break;
    case 10: $message = 'Invalid options were specified on the command line.'; break;
    case 11: $message = 'No matching files were found.'; $code="404 Not Found"; break;
    case 50: $message = 'The disk is (or was) full during extraction.'; break;
    case 51: $message = 'The end of the ZIP archive was encountered prematurely.'; break;
    case 80: $message = 'The user aborted unzip prematurely with control-C (or similar).'; break;
    case 81: $message = 'Testing or extraction of one or more files failed due to unsupported compression methods or unsupported decryption.'; break;
    case 82: $message = 'No files were found due to bad decryption password(s).'; break;
    default: $message = 'Unknown unzip error code.'; break;
    }

    $params['content'] = "Error code ".html_encode($exit_status)." - {$message}";
    if ($code) { $params['error'] = $code; }
}
