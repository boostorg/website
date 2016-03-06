<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Copyright 2016 Daniel James
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
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

    static function error_404($file, $message = null)
    {
        $error = "404 Not Found";

        header("{$_SERVER["SERVER_PROTOCOL"]} {$error}");

        $head = <<<HTML
      <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
      <title>Boost C++ Libraries - 404 Not Found</title>
HTML;

        $content = '<h1>'.html_encode($error).'</h1><p>File "' . html_encode($file) . '" not found.</p><p>';
        $content .= html_encode($message);
        $content .= '</p>';

        BoostFilter::display_template(Array('head' => $head, 'content' => $content));
    }
}
