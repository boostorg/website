<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost.php');

function get_archive_location(
    $pattern,
    $vpath,
    $archive_subdir = true,
    $zipfile = true,
    $archive_dir = ARCHIVE_DIR,
    $archive_file_prefix = ARCHIVE_FILE_PREFIX)
{
    $path_parts = array();
    preg_match($pattern, $vpath, $path_parts);

    $version = $path_parts[1];
    $key = $path_parts[2];

    $file = ($zipfile ? '' : $archive_dir . '/');

    if ($archive_subdir)
    {
        $file = $file . $archive_file_prefix . $version . '/' . $key;
    }
    else
    {
        $file = $file . $archive_file_prefix . $key;
    }

    $archive = $zipfile ? str_replace('\\','/', $archive_dir . '/' . $version . '.zip') : Null;
    
    return array(
        'version' => $version,
        'key' => $key,
        'file' => $file,
        'archive' => $archive,
        'zipfile' => $zipfile
    );
}

function display_from_archive(
    $params,
    $content_map = array(),
    $override_extractor = null)
{
    $params['template'] = dirname(__FILE__)."/template.php";
    $params['title'] = NULL;
    $params['charset'] = NULL;
    $params['content'] = NULL;
    
    $info_map = array_merge($content_map, array(
        array('@.*@','@[.](txt|py|rst|jam|v2|bat|sh|xml|qbk)$@i','text','text/plain'),
        array('@.*@','@[.](c|h|cpp|hpp)$@i','cpp','text/plain'),
        array('@.*@','@[.]png$@i','raw','image/png'),
        array('@.*@','@[.]gif$@i','raw','image/gif'),
        array('@.*@','@[.](jpg|jpeg|jpe)$@i','raw','image/jpeg'),
        array('@.*@','@[.]css$@i','raw','text/css'),
        array('@.*@','@[.]js$@i','raw','application/x-javascript'),
        array('@.*@','@[.]pdf$@i','raw','application/pdf'),
        array('@.*@','@[.](html|htm)$@i','raw','text/html'),
        array('@.*@','@[^.](Jamroot|Jamfile|ChangeLog)$@i','text','text/plain'),
        array('@.*@','@[.]dtd$@i','raw','application/xml-dtd'),
        ));

    $preprocess = null;
    $extractor = null;
    $type = null;

    foreach ($info_map as $i)
    {
        if (preg_match($i[1],$params['key']))
        {
            $extractor = $i[2];
            $type = $i[3];
            $preprocess = isset($i[4]) ? $i[4] : NULL;
            break;
        }
    }
    
    if ($override_extractor) $extractor = $override_extractor;

    if (!$extractor) {
        file_not_found($params);
        return;
    }

    // Check zipfile.

    $check_file = $params['zipfile'] ? $params['archive'] : $params['file'];

    if (!is_file($check_file)) {
        file_not_found($params,
            $params['zipfile'] ?
                'Unable to find zipfile.' :
                'Unable to find file.');
        return;        
    }

    $last_modified = max(
        strtotime("Thu, 19 Aug 2010 09:06:00 +0100"),
        filemtime($check_file));

    if (!conditional_get($last_modified))
        return;

    // Extract the file from the zipfile

    if ($params['zipfile'])
    {
        $unzip =
          UNZIP
          .' -p '.escapeshellarg($params['archive'])
          .' '.escapeshellarg($params['file']);

        if($extractor == 'raw') {
            display_raw_file($unzip, $type);
            return;
        }

        header('Expires: '.date(DATE_RFC2822, strtotime("+1 month")));
        header('Cache-Control: max-age=2592000'); // 30 days

        // Note: this sets $params['content'] with either the content or an error
        // message:
        if(!extract_file($unzip, $params['content'])) {
            file_not_found($params, $params['content']);
            return;
        }
    }
    else
    {
        if($extractor == 'raw') {
            display_unzipped_file($params['file'], $type);
            return;
        }

        header('Expires: '.date(DATE_RFC2822, strtotime("+1 month")));
        header('Cache-Control: max-age=2592000'); // 30 days

        $params['content'] = file_get_contents($params['file']);
    }
    
    if($type == 'text/html') {
        if(html_headers($params['content'])) {
            if($_SERVER['REQUEST_METHOD'] != 'HEAD') echo $params['content'];
            return;
        }
    }

    if($_SERVER['REQUEST_METHOD'] == 'HEAD') return;

    if ($preprocess) {
        $params['content'] = call_user_func($preprocess, $params['content']);
    }
    
    echo_filtered($extractor, $params);
}

function conditional_get($last_modified) {
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

class boost_archive_render_callbacks {
    var $content_callback, $params;
    
    function boost_archive_render_callbacks($content, $params) {
        $this->content_callback = $content;
        $this->archive = $params;
    }

    function content_head()
    {
        $charset = $this->archive['charset'] ? $this->archive['charset'] : 'us-ascii';
        $title = $this->archive['title'] ? 'Boost C++ Libraries - '.$this->archive['title'] : 'Boost C++ Libraries';

        print <<<HTML
<meta http-equiv="Content-Type" content="text/html; charset=${charset}" />
<title>${title}</title>
HTML;
    }
    
    function content()
    {
        if ($this->content_callback)
        {
            call_user_func($this->content_callback, $this->archive);
        }
    }
}

function display_raw_file($unzip, $type) {
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
        default:
            header('Expires: '.date(DATE_RFC2822, strtotime("+1 month")));
            header('Cache-Control: max-age=2592000'); // 30 days
    }

    // Since we're not returning a HTTP error for non-existant files,
    // might as well not bother checking for the file
    if($_SERVER['REQUEST_METHOD'] == 'HEAD') return;

    ## header('Content-Disposition: attachment; filename="downloaded.pdf"');
    $file_handle = popen($unzip,'rb');
    fpassthru($file_handle);
    $exit_status = pclose($file_handle);
    
    // Don't display errors for a corrupt zip file, as we seemd to
    // be getting them for legitimate files.

    if($exit_status > 3)
        echo 'Error extracting file: '.unzip_error($exit_status);
};

function display_unzipped_file($file, $type) {
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
    }

    // Since we're not returning a HTTP error for non-existant files,
    // might as well not bother checking for the file
    if($_SERVER['REQUEST_METHOD'] == 'HEAD') return;

    ## header('Content-Disposition: attachment; filename="downloaded.pdf"');
    $file_handle = fopen($file,'rb');
    // TODO: Check $file_handle (should be okay, because already checked for file).
    fpassthru($file_handle);
    $exit_status = fclose($file_handle);
    
    // TODO: What if !$exit_status?
};


function extract_file($unzip, &$content) {
    header('Expires: '.date(DATE_RFC2822, strtotime("+1 month")));
    header('Cache-Control: max-age=2592000'); // 30 days

    $file_handle = popen($unzip,'r');
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
        $content = strstr($_SERVER['HTTP_HOST'], 'beta') ? unzip_error($exit_status) : null;
        return false;
    }
}

//
// Filters
//

function echo_filtered($extractor, $params) {
    require_once(dirname(__FILE__)."/boost_filter_$extractor.php");
    $extractor_name = $extractor.'_filter';
    call_user_func($extractor_name, $params);
}

/* File Not Found */

function file_not_found($params, $message = null)
{
    if(is_string($params)) {
        $params = Array(
            'file' => $params,
            'template' => dirname(__FILE__)."/template.php"
        );
    }

    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    display_template($params['template'],
        new file_not_found_render_callbacks($params['file'],
            $params['zipfile'] ? "Unzip error: $message" : $message));
}

class file_not_found_render_callbacks
{
    var $file, $message;
    
    function file_not_found_render_callbacks($file, $message) {
        $this->file = $file;
        $this->message = $message;
    }

    function content_head()
    {
        print <<<HTML
  <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
  <title>Boost C++ Libraries - 404 Not Found</title>
HTML;
    }
    
    function content()
    {
        print '<h1>404 Not Found</h1><p>File "' . $this->file . '" not found.</p>';
        if($this->message) {
            print '<p>'.htmlentities($this->message).'</p>';
        }
    }
}

/*
 * HTML processing functions
 */

function html_headers($content)
{
    if(preg_match(
        '@<meta\s+http-equiv\s*=\s*["\']?refresh["\']?\s+content\s*=\s*["\']0;\s*URL=([^"\']*)["\']\s*/?>@i',
        $content,
        $redirect))
    {
        header('Location: '.resolve_url($redirect[1]), TRUE, 301);
        return true;
    }
}

// Not a full implementation. Just good enough for redirecting.
function resolve_url($url) {
    $url = parse_url($url);

    if(isset($url['schme'])) return $url;

    $url['scheme'] = 'http'; # Detect other schemes?

    if(!isset($url['host'])) {
        $url['host'] = $_SERVER['SERVER_NAME'];
        
        if($url['path'][0] != '/') {
            $path = explode('/', $_SERVER['REQUEST_URI']);
            array_pop($path);
            $rel_path = explode('/', $url['path']);
            while(isset($rel_path[0]) && $rel_path[0] == '..') {
                array_pop($path);
                array_shift($rel_path);
            }
            $url['path'] = implode('/', $path).'/'.implode('/', $rel_path);
        }
    }
    
    return $url['scheme'].'://'.$url['host'] . $url['path'];
}

// Display the content in the standard boost template

function display_template($template, $callbacks) {
    $_file = $callbacks;
    include($template);
}

// Return a readable error message for unzip exit state.

function unzip_error($exit_status) {
    switch($exit_status) {
    case 0: return 'No error.';
    case 1: return 'One  or  more  warning  errors  were  encountered.';
    case 2: return 'A generic error in the zipfile format was detected.';
    case 3: return 'A severe error in the zipfile format was detected.';
    case 4: return 'Unzip was unable to allocate memory for one or more buffers during program initialization.';
    case 5: return 'Unzip was unable to allocate memory or unable to obtain a tty to read the decryption password(s).';
    case 6: return 'Unzip was unable to allocate memory during decompression to disk.';
    case 7: return 'Unzip was unable to allocate memory during in-memory decompression.';
    case 9: return 'The specified zipfile was not found.';
    case 10: return 'Invalid options were specified on the command line.';
    case 11: return 'No matching files were found.';
    case 50: return 'The disk is (or was) full during extraction.';
    case 51: return 'The end of the ZIP archive was encountered prematurely.';
    case 80: return 'The user aborted unzip prematurely with control-C (or similar).';
    case 81: return 'Testing or extraction of one or more files failed due to unsupported compression methods or unsupported decryption.';
    case 82: return 'No files were found due to bad decryption password(s).';
    default: return 'Unknown unzip error code: ' + $exit_status;
    }
}
