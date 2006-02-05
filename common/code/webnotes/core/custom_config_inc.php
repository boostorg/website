<?php
/*
  Copyright 2005 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

if ($_SERVER['HTTP_HOST'] === 'boost.sourceforge.net') {
    require '';
}
else if ($_SERVER['HTTP_HOST'] === 'boost.borg.redshift-software.com') {
    require 'C:/DevRoots/Boost/Boost_webnotes_inc.php';
}
else if ($_SERVER['HTTP_HOST'] === 'boost.redshift-software.com') {
    require '/export/website/boost/Boost_webnotes_inc.php';
}

$g_web_directory = '/common/code/webnotes/';
$g_theme = 'clean';
$g_enable_email_notification = OFF;

?>