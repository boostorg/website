<?php

/**
 * Check that error reporting works for a weak error_reporting.
 *
 * @httpCode 500
 * @exitCode 255
 */

error_reporting(0);
require_once(__DIR__.'/../../../bootstrap.php');
error_reporting(E_WARNING);
file_get_contents(__DIR__.'/does-not-exist.txt');
