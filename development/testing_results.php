<?php
require_once(dirname(__FILE__) . '/../common/code/archive_file.php');

$_file = new archive_file(
  '/^[\/]([^\/]+)[\/](.*)$/',$_SERVER["PATH_INFO"],
  true, // we always want raw output
  false, // the result zips don't have the tag subdir
  RESULTS_DIR."/",
  ""
  );
?>
