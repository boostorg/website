<?php

/* NOTE: Not including boost.php, we don't want the autoloaded running,
 *       we just want to know where to find the implementation. */
require_once(__DIR__.'/../common/code/boost_config.php');

if (!defined('BOOST_TASKS_DIR')) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    echo "Tasks directory not set.";
    exit(1);
}

require_once(BOOST_TASKS_DIR.'/webhook/webhook.php');

webhook();
