<?php
/*
  Copyright 2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost.php');

switch ($_SERVER["PATH_INFO"])
{
  case '/boost-build.tar.bz2': $fname = 'boost-build.tar.bz2'; $ftype = 'application/x-bzip'; break;
  case '/boost-build.zip': $fname = 'boost-build.zip'; $ftype = 'application/zip'; break;
}
if (isset($fname))
{
  header('Content-Type: '.$ftype);
  header("Content-Length: " . filesize(ARCHIVE_DIR.'/'.$fname));
  header('Content-Disposition: attachment; filename="'.$fname.'"');
  $file_handle = fopen(ARCHIVE_DIR.'/'.$fname,'rb');
  fpassthru($file_handle);
  fclose($file_handle);
}
?>
