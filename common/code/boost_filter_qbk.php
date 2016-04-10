<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

class BoostFilterQbk extends BoostFilterText
{
    function echo_filtered() {
        $this->title = html_encode($this->data->key);
        $this->data->noindex = true;

        $this->display_template(
            $this->template_params($this->qbk_filter_content()));
    }

    function qbk_filter_content()
    {
        return
            "<h3>".html_encode($this->data->key)."</h3>\n".
            "<pre>\n".
            $this->encoded_text('cpp').
            "</pre>\n";
    }
}
