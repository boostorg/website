<?php

require_once(dirname(__FILE__) . '/../common/code/boost.php');

$archive = new BoostArchive(array(
    'pattern' => '/^[\/]([^\/]+)[\/](.*)$/',
    'archive_dir' => RESULTS_DIR,
));

$archive->display_from_archive(
  array(
  //~ array(path-regex,raw|simple|text|cpp,mime-type),
  )
);
