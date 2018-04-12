<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/

class BoostFilterBasic extends BoostFilter
{
    function echo_filtered()
    {
        $text = $this->content;

        $match = null;

        if(preg_match('@(?:</head>\s*)?<body[^>]*>@is', $text, $match, PREG_OFFSET_CAPTURE)) {
            $is_xhtml = preg_match('@<!DOCTYPE[^>]*xhtml@i', $match[0][0]);
            $tag_end = $is_xhtml ? '/>' : '>';

            $head = substr($text, 0, $match[0][1]);
            $is_asciidoctor = strpos($head, '<meta name="generator" content="Asciidoctor') !== false;
            echo $this->alter_title($head);
            echo '<link rel="icon" href="/favicon.ico" type="image/ico"'.$tag_end;
            echo '<link rel="stylesheet" type="text/css" href="/style-v2/section-basic.css"'.$tag_end;
            if ($is_asciidoctor) {
                echo str_replace('class="', 'class="boost-asciidoctor ', $match[0][0]);
            } else {
                if (!preg_match('@<meta\b[^<>]*\bname\s*=\s*["\']?viewport\b@', $head)) {
                    echo '<meta name="viewport" content="width=device-width,initial-scale=1.0"/>';
                }
                echo $match[0][0];
            }
            virtual("/common/heading-doc.html");
            echo latest_link($this->data);
            echo $this->prepare_html($this->remove_html_banner(substr($text, $match[0][1] + strlen($match[0][0]))));
        }
        else {
            echo $text;
        }
    }
}
