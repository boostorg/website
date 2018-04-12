<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Copyright 2016 Daniel James
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/

// Some miscellaneous functions for serving web pages....

class BoostWeb
{
    // HTTP header handling

    static function http_headers($type, $last_modified, $expires = null)
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

        return static::conditional_get($last_modified);
    }

    static function conditional_get($last_modified)
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

    static function throw_http_error($status_code, $message, $sub_message = null)
    {
        throw new BoostWeb_HttpError($status_code, $message, $sub_message, null);
    }

    static function throw_error_404($file, $message = null)
    {
        throw new BoostWeb_HttpError(404, 'Not Found', $message, $file);
    }


    static function return_error($e) {
        $error = "{$e->status_code} {$e->status_message}";
        $error_html = html_encode($error);

        header("{$_SERVER["SERVER_PROTOCOL"]} {$error}");

        $head = <<<HTML
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title>Boost C++ Libraries - {$error_html}</title>
HTML;

        $content = "<h1>{$error_html}</h1>\n";
        if ($e->file) {
            $content .= '<p>File "' . html_encode($e->file) . '" not found.</p>';
        }
        if ($e->sub_message) {
            $content .= "<p>".html_encode($e->sub_message)."</p>";
        }

        BoostFilter::display_template(Array('head' => $head, 'content' => $content));
    }
}

class BoostWeb_HttpError extends BoostException {
    var $status_code;
    var $status_message;
    var $sub_message;
    var $file;

    function __construct($status_code, $status_message, $sub_message, $file) {
        $this->status_code = $status_code;
        $this->status_message = $status_message;
        $this->sub_message = $sub_message;
        $this->file = $file;
        parent::__construct("HTTP error: {$this->status_code} {$this->status_message}");
    }
}
