<?php

/*
  Copyright 2005-2008 Redshift Software, Inc.
  Copyright 2016 Daniel James
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

require_once(dirname(__FILE__) . '/boost.php');

class BoostArchive
{
    var $params;

    function __construct($params = Array()) {
        $this->params = $params;
    }

    function get_param($key, $default) {
        return array_key_exists($key, $this->params) ? $this->params[$key] : $default;
    }

    function display_from_archive()
    {
        // Set default values

        $pattern = $this->get_param('pattern', '@^[/]([^/]+)[/](.*)$@');
        $archive_dir = $this->get_param('archive_dir', STATIC_DIR);
        $archive_file_prefix = $this->get_param('archive_file_prefix', ARCHIVE_FILE_PREFIX);

        // Get the archive location.

        $path_parts = array();
        preg_match($pattern, $_SERVER["PATH_INFO"], $path_parts);

        $zipfile_name = $path_parts[1];
        $path_in_zipfile = $archive_file_prefix . $path_parts[2];

        $archive_file =
                str_replace('\\','/', $archive_dir . '/' . $zipfile_name . '.zip');

        // Check file exists.

        if (!is_readable($archive_file)) {
            BoostWeb::throw_error_404($path_in_zipfile, 'Unable to find zipfile.');
        }

        // Choose mime type to use
        // For reference, list of apache's default mime types:
        // http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types

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

        $extension = pathinfo($path_in_zipfile, PATHINFO_EXTENSION);
        $type = array_key_exists($extension, $mime_types) ? $mime_types[$extension] : 'text/plain';

        // Handle ETags and Last-Modified HTTP headers.

        // Output raw files.

        if (!BoostWeb::http_headers($type, filemtime($archive_file)))
            return;

        $this->display_raw_file($archive_file, $path_in_zipfile);
    }

    function display_raw_file($archive, $file)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        ## header('Content-Disposition: attachment; filename="downloaded.pdf"');
        $file_handle = popen($this->unzip_command($archive, $file), 'rb');
        fpassthru($file_handle);
        $exit_status = pclose($file_handle);

        // Don't display errors for a corrupt zip file, as we seemd to
        // be getting them for legitimate files.

        if($exit_status > 1) {
            $this->unzip_error($exit_status, $archive);
        }
    }

    function unzip_command($archive, $path) {
        return UNZIP
          .' -p '.escapeshellarg($archive)
          .' '.escapeshellarg($path);
    }

    function unzip_error($exit_status, $archive) {
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
            if ($archive && is_file($archive)) {
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

        if ($code) {
            header("{$_SERVER["SERVER_PROTOCOL"]} {$code}", true);
        }

        echo "Error extracting file: Error code ".html_encode($exit_status)." - {$message}";
    }
}
