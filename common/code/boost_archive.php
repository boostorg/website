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

        if ($this->params['fix_dir']) {
            $fix_path = "{$this->params['fix_dir']}/{$version_dir}/{$this->params['key']}";

            if (is_file($fix_path) ||
                (is_dir($fix_path) && is_file("{$fix_path}/index.html")))
            {
                $this->params['zipfile'] = false;
                $file = $fix_path;
            }
        }

        if (!$file) {
            $file = ($this->params['zipfile'] ? '' : $this->params['archive_dir'] . '/');

            if ($this->params['archive_subdir'])
            {
                $file = $file . $this->params['archive_file_prefix'] . $version_dir . '/' . $this->params['key'];
            }
            else
            {
                $file = $file . $this->params['archive_file_prefix'] . $this->params['key'];
            }
        }

        $this->params['file'] = $file;

        $this->params['archive'] = $this->params['zipfile'] ?
                str_replace('\\','/', $this->params['archive_dir'] . '/' . $version_dir . '.zip') :
                Null;
    }

    function display_from_archive($content_map = array())
    {
        // Set default values

        $this->params = array_merge(
            array(
                'pattern' => '@^[/]([^/]+)[/](.*)$@',
                'vpath' => $_SERVER["PATH_INFO"],
                'archive_subdir' => true,
                'zipfile' => true,
                'fix_dir' => false,
                'archive_dir' => ARCHIVE_DIR,
                'archive_file_prefix' => ARCHIVE_FILE_PREFIX,
                'use_http_expire_date' => false,
                'override_extractor' => null,
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

        if ($this->params['zipfile'])
        {
            $check_file = $this->params['archive'];

            if (!is_readable($check_file)) {
                file_not_found($this->params, 'Unable to find zipfile.');
                return;
            }
        }
        else
        {
            $check_file = $this->params['file'];

            if (is_dir($check_file))
            {
                if(substr($check_file, -1) != '/') {
                    $redirect = resolve_url(basename($check_file).'/');
                    header("Location: $redirect", TRUE, $redirect_status_code);
                    return;
                }

                $found_file = NULL;
                if (is_readable("$check_file/index.html")) $found_file = 'index.html';
                else if (is_readable("$check_file/index.htm")) $found_file = 'index.htm';

                if ($found_file) {
                    $this->params['file'] = $check_file = $check_file.$found_file;
                    $this->params['key'] = $this->params['key'].$found_file;
                }
                else {
                    if (!http_headers('text/html', filemtime($check_file), $expires))
                        return;

                    $display_dir = new BoostDisplayDir($this->params);
                    return $display_dir->display($check_file);
                }
            }
            else if (!is_readable($check_file)) {
                file_not_found($this->params, 'Unable to find file.');
                return;
            }
        }

        // Choose filter to use

        $info_map = array_merge($content_map, array(
            array('','@[.](txt|py|rst|jam|v2|bat|sh|xml|xsl|toyxml)$@i','text','text/plain'),
            array('','@[.](qbk|quickbook)$@i','qbk','text/plain'),
            array('','@[.](c|h|cpp|hpp)$@i','cpp','text/plain'),
            array('','@[.]png$@i','raw','image/png'),
            array('','@[.]gif$@i','raw','image/gif'),
            array('','@[.](jpg|jpeg|jpe)$@i','raw','image/jpeg'),
            array('','@[.]svg$@i','raw','image/svg+xml'),
            array('','@[.]css$@i','raw','text/css'),
            array('','@[.]js$@i','raw','application/x-javascript'),
            array('','@[.]pdf$@i','raw','application/pdf'),
            array('','@[.](html|htm)$@i','raw','text/html'),
            array('','@(/|^)(Jamroot|Jamfile|ChangeLog|configure)$@i','text','text/plain'),
            array('','@[.]dtd$@i','raw','application/xml-dtd'),
            ));

        $preprocess = null;
        $extractor = null;
        $type = null;

        foreach ($info_map as $i)
        {
            if (preg_match($i[1],$this->params['key']))
            {
                if ($i[0]) {
                    $version = BoostVersion::from($i[0]);
                    if ($version->compare(BoostVersion::page()) > 0) {
                        // This is after the current version.
                        continue;
                    }
                }

                $extractor = $i[2];
                $type = $i[3];
                $preprocess = isset($i[4]) ? $i[4] : NULL;
                break;
            }
        }

        if ($this->params['override_extractor'])
            $extractor = $this->params['override_extractor'];

        if (!$extractor) {
            if (strpos($_SERVER['HTTP_HOST'], 'www.boost.org') === false) {
                file_not_found($this->params,
                    "No extractor found for filename.");
            } else {
                file_not_found($this->params);
            }
            return;
        }

        // Handle ETags and Last-Modified HTTP headers.

        // Output raw files.

        if($extractor == 'raw') {
            if (!http_headers($type, filemtime($check_file), $expires))
                return;

            if($_SERVER['REQUEST_METHOD'] != 'HEAD')
                display_raw_file($this->params, $type);
        }
        else {
            // Read file from hard drive or zipfile

            // Note: this sets $this->params['content'] with either the content or an error
            // message:
            if(!extract_file($this->params, $this->params['content'])) {
                file_not_found($this->params, $this->params['content']);
                return;
            }

            // Check if the file contains a redirect.

            if($type == 'text/html') {
                if($redirect = detect_redirect($this->params['content'])) {
                    http_headers('text/html', null, "+1 day");
                    header("Location: $redirect", TRUE, $redirect_status_code);
                    if($_SERVER['REQUEST_METHOD'] != 'HEAD') echo $this->params['content'];
                    return;
                }
            }

            if (!http_headers('text/html', filemtime($check_file), $expires))
                return;

            // Finally process the file and display it.

            if($_SERVER['REQUEST_METHOD'] != 'HEAD') {
                if ($preprocess) {
                    $this->params['content'] = call_user_func($preprocess, $this->params['content']);
                }

                echo_filtered($extractor, $this->params);
            }
        }
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

function display_raw_file($params, $type)
{
    ## header('Content-Disposition: attachment; filename="downloaded.pdf"');
    if($params['zipfile']) {
        $file_handle = popen(unzip_command($params), 'rb');
        fpassthru($file_handle);
        $exit_status = pclose($file_handle);
    
        // Don't display errors for a corrupt zip file, as we seemd to
        // be getting them for legitimate files.

        if($exit_status > 3)
            echo 'Error extracting file: '.unzip_error($params, $exit_status);
    }
    else {
        readfile($params['file']);
    }
}

function extract_file($params, &$content) {
    if($params['zipfile']) {
        $file_handle = popen(unzip_command($params),'r');
        $text = '';
        while ($file_handle && !feof($file_handle)) {
            $text .= fread($file_handle,8*1024);
        }
        $exit_status = pclose($file_handle);

        if($exit_status == 0) {
            $content = $text;
            return true;
        }
        else {
            $content = strstr($_SERVER['HTTP_HOST'], 'beta') ? unzip_error($params, $exit_status) : null;
            return false;
        }
    }
    else {
        $content = file_get_contents($params['file']);
        return true;
    }
}

function unzip_command($params) {
    return
      UNZIP
      .' -p '.escapeshellarg($params['archive'])
      .' '.escapeshellarg($params['file']);
}

//
// Filters
//

function echo_filtered($extractor, $params) {
    $name = "BoostFilter".underscore_to_camel_case($extractor);
    $extractor = new $name($params);
    $extractor->echo_filtered();
}

function underscore_to_camel_case($name) {
    return str_replace(' ','', ucwords(str_replace('_', ' ', $name)));
}

/* File Not Found */

function file_not_found($params, $message = null)
{
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    if (!$params['error']) { $params['error'] = 404; }

    $head = <<<HTML
  <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
  <title>Boost C++ Libraries - 404 Not Found</title>
HTML;

    $content = '<h1>404 Not Found</h1><p>File "' . $params['file'] . '" not found.</p><p>';
    if(!empty($params['zipfile'])) $content .= "Unzip error: ";
    $content .= html_encode($message);
    $content .= '</p>';

    $filter = new BoostFilters($params);
    $filter->display_template(Array('head' => $head, 'content' => $content));
}

/*
 * HTML processing functions
 */

function detect_redirect($content)
{
    // Only check small files, since larger files are never redirects, and are
    // expensive to search.
    if(strlen($content) <= 2000 &&
        preg_match(
            '@<meta\s+http-equiv\s*=\s*["\']?refresh["\']?\s+content\s*=\s*["\']0;\s*URL=([^"\']*)["\']\s*/?>@i',
            $content, $redirect))
    {
        return resolve_url($redirect[1]);
    }

    return false;
}

function latest_link($params)
{
    if (!isset($params['version']) || $params['error']) {
        return;
    }

    $version = BoostVersion::from($params['version']);

    $current = BoostVersion::current();
    switch ($current->compare($version))
    {
    case 0:
        break;
    case 1:
        echo '<div class="boost-common-header-notice">';
        if (realpath("{$params['archive_dir']}/{$current->dir()}/$params[key]") !== false)
        {
            echo '<a class="boost-common-header-inner" href="/doc/libs/release/',$params['key'],'">',
                "Click here to view the latest version of this page.",
                '</a>';
        }
        else
        {
            echo '<a class="boost-common-header-inner" href="/doc/libs/">',
                "This is an old version of boost. ",
                "Click here for the latest version's documentation home page.",
                '</a>';
        }
        echo '</div>', "\n";
        break;
    case -1:
        echo '<div class="boost-common-header-notice">';
        echo '<span class="boost-common-header-inner">';
        echo 'This is the documentation for a development version of boost';
        echo '</span>';
        echo '</div>', "\n";
        break;
    }
}

// Return a readable error message for unzip exit state.

function unzip_error($params, $exit_status) {
    switch($exit_status) {
    case 0: $message = 'No error.'; break;
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
    case 11: $message = 'No matching files were found.'; break;
    case 50: $message = 'The disk is (or was) full during extraction.'; break;
    case 51: $message = 'The end of the ZIP archive was encountered prematurely.'; break;
    case 80: $message = 'The user aborted unzip prematurely with control-C (or similar).'; break;
    case 81: $message = 'Testing or extraction of one or more files failed due to unsupported compression methods or unsupported decryption.'; break;
    case 82: $message = 'No files were found due to bad decryption password(s).'; break;
    default: $message = 'Unknown unzip error code.'; break;
    }

    return "Error code ".html_encode($exit_status)." - {$message}";
}
