<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

require_once(dirname(__FILE__).'/boost_filter_text.php');

function qbk_filter($params) {
    $params['title'] = htmlentities($params['key']);
    $params['noindex'] = true;

    display_template($params['template'],
        boost_archive_render_callbacks(qbk_filter_content($params), $params));
}

function qbk_filter_content($params)
{
    return
        "<h3>".htmlentities($params['key'])."</h3>\n".
        "<pre>\n".
        encoded_text($params, 'qbk').
        "</pre>\n";
}
