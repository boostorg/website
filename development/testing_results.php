<?php
require_once(dirname(__FILE__) . '/../common/code/boost_archive.php');

display_from_archive(
  get_archive_location(
    '/^[\/]([^\/]+)[\/](.*)$/',$_SERVER["PATH_INFO"],
    false, // the result zips don't have the tag subdir
    RESULTS_DIR
  ),
  array(
  //~ array(version-regex,path-regex,raw|simple|text|cpp|boost_book_html|boost_libs_html,mime-type),
  ),
  'raw' // we always want raw output
);
