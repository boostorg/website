<?php

require_once(dirname(__FILE__) . '/../common/code/bootstrap.php');

$archive = new BoostArchive(array(
    'pattern' => '/^[\/]([^\/]+)[\/](.*)$/',
    'archive_dir' => RESULTS_DIR,
));

$archive->display_from_archive();
