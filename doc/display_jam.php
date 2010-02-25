<?php
require_once(dirname(__FILE__) . '/../common/code/boost_archive.php');

display_from_archive(
  get_archive_location('@^[/]([^/]+)[/](.*)$@',$_SERVER["PATH_INFO"]),
  array(
  //~ array(version-regex,path-regex,raw|simple|text|cpp|boost_book_html|boost_libs_html,mime-type[,preprocess hook]),
  array('@.*@','@[.](html|htm)$@i','boost_book_html','text/html'),
));
