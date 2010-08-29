<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

require_once(dirname(__FILE__).'/boost_filters.php');

function basic_filter($params)
{
    $text = remove_html_banner($params['content']);

    $is_xhtml = preg_match('@<!DOCTYPE[^>]*xhtml@i', $text);
    $tag_end = $is_xhtml ? '/>' : '>';
    
    $match = null;
    
    if(preg_match('@(?:</head>\s*)?<body[^>]*>@is', $text, $match, PREG_OFFSET_CAPTURE)) {
        echo substr($text, 0, $match[0][1]);
        echo '<link rel="icon" href="/favicon.ico" type="image/ico"'.$tag_end;
        echo '<link rel="stylesheet" type="text/css" href="/style-v2/section-basic.css"'.$tag_end;
        echo $match[0][0];
        virtual("/common/heading-doc.html");
        echo prepare_html(substr($text, $match[0][1] + strlen($match[0][0])));
        
    }
    else {
        echo $text;
    }
}
