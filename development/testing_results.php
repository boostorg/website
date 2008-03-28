<?php
require_once(dirname(__FILE__) . '/../common/code/boost_archive.php');

$_file = new boost_archive(
  '/^[\/]([^\/]+)[\/](.*)$/',$_SERVER["PATH_INFO"],
  array(
  //~ array(version-regex,path-regex,raw|simple|text|cpp|boost_book_html|boost_libs_html,mime-type),
    ),
  true, // we always want raw output
  false, // the result zips don't have the tag subdir
  RESULTS_DIR);
?>
