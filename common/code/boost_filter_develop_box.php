<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/

class BoostFilterDevelopBox extends BoostFilter
{
    function echo_filtered()
    {
        $text = $this->content;
        $box = latest_link($this->data);
        if ($box && preg_match('@(?:</head>\s*)?<body[^>]*>@is', $text, $match, PREG_OFFSET_CAPTURE)) {
            $is_xhtml = preg_match('@<!DOCTYPE[^>]*xhtml@i', $match[0][0]);
            $tag_end = $is_xhtml ? '/>' : '>';
            $head = substr($text, 0, $match[0][1]);
            echo $this->alter_title($head);
            echo '<link rel="icon" href="/favicon.ico" type="image/ico"'.$tag_end;
            echo '<link rel="stylesheet" type="text/css" href="/style-v2/section-basic.css"'.$tag_end;
            echo $match[0][0];
            echo $box;
            echo $this->prepare_html(substr($text, $match[0][1] + strlen($match[0][0])));
        } else {
            echo $text;
        }
    }
}

