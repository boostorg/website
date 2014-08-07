<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

class BoostFilterQbk extends BoostFilters
{
    function echo_filtered($params) {
        $params['title'] = html_encode($params['key']);
        $params['noindex'] = true;

        display_template($params,
            boost_archive_render_callbacks(
                $this->qbk_filter_content($params), $params));
    }

    function qbk_filter_content($params)
    {
        return
            "<h3>".html_encode($params['key'])."</h3>\n".
            "<pre>\n".
            (new BoostFilterText())->encoded_text($params, 'cpp').
            "</pre>\n";
    }
}
