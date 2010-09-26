<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

require_once(dirname(__FILE__).'/boost_filters.php');

function boost_frame1_filter($params) {
    html_init($params);
    display_template($params['template'],
        boost_archive_render_callbacks(boost_frame1_filter_content($params), $params));
}

function boost_frame1_filter_content($params)
{
    $text = prepare_html($params['content'], true);
    
    $text = substr($text,strpos($text,'<div class="spirit-nav">'));
    $text = substr($text,0,strpos($text,'</body>'));
    for ($i = 0; $i < 8; $i++) {
        $text = preg_replace(
            '@<img src="[\./]*images/(.*\.png)" alt="(.*)"([ ][/])?>@Ssm',
            '<img src="/style-v2/css_0/${1}" alt="${2}" />',
            $text );
    }
    $text = str_replace('<hr>','',$text);
    $text = str_replace('<table width="100%">','<table class="footer-table">',$text);
    $text = preg_replace(
        '@[\s]+(border|cellpadding|cellspacing|width|height|valign|frame|rules|naturalsizeflag|background)=[^\s>]+@i',
        '',
        $text );
    
    return $text;
}
