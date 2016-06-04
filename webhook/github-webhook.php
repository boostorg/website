<?php

/* NOTE: Not including boost.php, we don't want the autoloaded running,
 *       we just want to know where to find the implementation. */
require_once(__DIR__.'/../common/code/boost_config.php');

function boost_website_webhook() {
    /* Check the payload */
    if (!array_key_exists('payload', $_POST)) {
        die("Unable to find payload.\n");
    }

    $payload = json_decode($_POST['payload']);
    if (!$payload) {
        die("Error parsing payload.\n");
    }

    /* Get github headers */
    $github_headers = array();
    foreach($_SERVER as $key => $value) {
        if (preg_match('@^HTTP_X_GITHUB_(.*)$@', $key, $match)) {
            $github_headers[strtolower($match[1])] = $value;
        }
    }

    $signature = array_key_exists('X_HUB_SIGNATURE', $_SERVER) ?
        $_SERVER['X_HUB_SIGNATURE'] : false;

    require_once(BOOST_TASKS_DIR.'/webhook/webhook.php');

    if (!array_key_exists('event', $github_headers)) {
        die("No event header.\n");
    }

    webhook($github_headers, $payload, $signature);
}

boost_website_webhook();
