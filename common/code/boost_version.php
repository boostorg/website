<?php
/*
  Copyright 2005-2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

function boost_version($v,$r,$p)
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
?>
