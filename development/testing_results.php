<?php
require_once(dirname(__FILE__) . '/../common/code/boost_archive.php');

$_file = new archive_file(
  '/^[\/]([^\/]+)[\/](.*)$/',$_SERVER["PATH_INFO"],array(),
  true, // we always want raw output
  false, // the result zips don't have the tag subdir
  RESULTS_DIR);
?>
