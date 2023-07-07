<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/

class BoostFilterBoostBookBasic extends BoostFilter
{
    function echo_filtered()
    {
        $text = $this->content;

        $match = null;

        $pos1 = strpos($text, '</head>');
        $pos2 = strpos($text, '<body', $pos1);
        $pos3 = strpos($text, '>', $pos2) + 1;
        $pos4 = strpos($text, '<div class="spirit-nav">', $pos3);
        $head = $this->alter_title(substr($text, 0, $pos1));
        echo $head;
        echo '<link rel="icon" href="/favicon.ico" type="image/ico"/>';
        echo '<link rel="stylesheet" type="text/css" href="/style-v2/section-basic.css"/>';
        if (!preg_match('@<meta\b[^<>]*\bname\s*=\s*["\']?viewport\b@', $head)) {
            echo '<meta name="viewport" content="width=device-width,initial-scale=1.0"/>';
        }
        echo substr($text, $pos1, $pos3 - $pos1);
        virtual("/common/heading-doc.html");
        echo latest_link($this->data);

        $text = preg_replace('@(<div[^>]* )title="[^"]*"([^>]*>)@', '$1$2', substr($text, $pos4));
        echo $this->prepare_html($text);
    }
}
