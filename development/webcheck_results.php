<?php
/*
  Copyright 2007 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/
$p=substr($_SERVER["ORIG_PATH_INFO"],1);
if ($p)
{
  if (preg_match('@[.](html|htm)$@i',$p)) { header('Content-type: text/html'); }
  else if (preg_match('@[.](css)$@i',$p)) { header('Content-type: text/css'); }
  else if (preg_match('@[.](js)$@i',$p)) { header('Content-type: application/x-javascript'); }
  readfile("/home/grafik/www.boost.org/testing/webcheck/".basename($p));
}
?>
