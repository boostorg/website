<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

require_once(dirname(__FILE__).'/boost_filters.php');

function basic_filter($params)
{
    $text = prepare_html($params['content']);
    $text = remove_html_banner($text);

    $is_xhtml = preg_match('@<!DOCTYPE[^>]*xhtml@i', $text);
    $tag_end = $is_xhtml ? '/>' : '>';
    
    $sections = preg_split('@(</head>|<body[^>]*>)@i',$text,-1,PREG_SPLIT_DELIM_CAPTURE);

    $body_index = 0;
    $index = 0;
    foreach($sections as $section) {
        if(stripos($section, '<body') === 0) {
            $body_index = $index;
            break;
        }
        ++$index;
    }

    if(!$body_index) {
        print($text);
    }
    else {
        $index = 0;
        foreach($sections as $section) {
            print($section);
            if($index == 0) {
                print '<link rel="icon" href="/favicon.ico" type="image/ico"'.$tag_end;
                print '<link rel="stylesheet" type="text/css" href="/style-v2/section-basic.css"'.$tag_end;
            }
            else if($index == $body_index) {
                virtual("/common/heading-doc.html");
            }
            ++$index;
        }
    }
}
