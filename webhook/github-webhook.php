<?php

/* NOTE: Not including boost.php, we don't want the autoloaded running,
 *       we just want to know where to find the implementation. */
require_once(__DIR__.'/../common/code/boost_config.php');

if (!defined('BOOST_TASKS_DIR')) {
    die("Tasks directory not set.");
}

require_once(BOOST_TASKS_DIR.'/webhook/webhook.php');

webhook();
