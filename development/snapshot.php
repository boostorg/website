<?php
/*
  Copyright 2007 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
$branch=substr($_SERVER["PATH_INFO"],1);
if ($branch)
{
  header('Content-type: application/x-gtar');
  header('Content-Disposition: attachment; filename="boost-snapshot.tar.bz2"');
  $file_handle = popen("/home/grafik/www.boost.org/boost_svn_export_archive.sh ".$branch,'rb');
  fpassthru($file_handle);
  pclose($file_handle);
}
?>