<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

class BoostFilterQbk extends BoostFilterText
{
    function __construct($data) {
        parent::_construct($data);
        $this->data->noindex = true;
    }

    function filter_content()
    {
        return
            "<h3>".html_encode($this->data->path)."</h3>\n".
            "<pre>\n".
            $this->encoded_text('cpp').
            "</pre>\n";
    }
}
