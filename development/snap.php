<?php
/*
  Copyright 2007 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
header('Content-type: text/plain');
$file_handle = popen("ls -laF /tmp",'r');
fpassthru($file_handle);
pclose($file_handle);
?>