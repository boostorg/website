<?php
/*
  Copyright 2005-2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost.php');

$boost_current_version = Array(1,43,0);

function boost_title()
{
    $vinfo = array();
    if (isset($_SERVER["PATH_INFO"])) {
        preg_match('@([0-9]+)_([0-9]+)_([0-9]+)@',$_SERVER["PATH_INFO"],$vinfo);
    }
    if (isset($vinfo[0])) {
        array_shift($vinfo);
        return 'Boost '.implode('.', $vinfo);
    }
    else {
        return 'Boost';
    }
}

function boost_version($v,$r,$p)
{
    if (isset($_SERVER["PATH_INFO"]))
    {
        $vinfo = array();
        preg_match('@([0-9]+)_([0-9]+)_([0-9]+)@',$_SERVER["PATH_INFO"],$vinfo);
        if (isset($vinfo[0]))
        {
            return
              ($v < $vinfo[1]) ||
              ($v == $vinfo[1] && $r < $vinfo[2]) ||
              ($v == $vinfo[1] && $r == $vinfo[2] && $p <= $vinfo[3]);
        }
        else
        {
            return FALSE;
        }
    }
    else
    {
        return TRUE;
    }
}

function boost_future_version($version)
{
    if ($version)
    {
        $vinfo = array();
        preg_match('@([0-9]+)_([0-9]+)_([0-9]+)@',$version,$vinfo);
        if (isset($vinfo[0]))
        {
            global $boost_current_version;
            $v = $boost_current_version[0];
            $r = $boost_current_version[1];
            $p = $boost_current_version[2];
            return
              ($v < $vinfo[1]) ||
              ($v == $vinfo[1] && $r < $vinfo[2]) ||
              ($v == $vinfo[1] && $r == $vinfo[2] && $p < $vinfo[3]);
        }
        else
        {
            return FALSE;
        }
    }
    else
    {
        return FALSE;
    }
}
