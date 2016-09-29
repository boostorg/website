<?php

/* Bootstrap script for boost web pages.
 * Command line/external code should include 'boost.php'
 */

// Set timezone to UTC.
date_default_timezone_set('UTC');

// Die on all errors.
function error_handler($message) {
    if (array_key_exists('SERVER_PROTOCOL', $_SERVER)) {
        @header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        echo htmlentities($message),"\n";
    }
    else if (defined('STDERR')) {
        fputs(STDERR, "{$message}\n");
    }
    else {
        echo "{$message}\n";
    }

    exit(1);
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_handler("{$errfile}:{$errline}: {$errstr}");
});

set_exception_handler(function($e) {
    error_handler($e);
});

// Fatal errors aren't caught by the error handler, so make sure they
// return an internal server error.
register_shutdown_function(function() {
    $last_error = error_get_last();
    if ($last_error && $last_error['type'] & (E_ERROR|E_PARSE|E_CORE_ERROR|E_COMPILE_ERROR)) {
        if (array_key_exists('SERVER_PROTOCOL', $_SERVER)) {
           header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        }
    }
});

// General setup.
require_once(__DIR__.'/boost.php');
