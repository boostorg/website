<?php

/**
 * Check that the error reporter catches an error
 *
 * @httpCode 500
 * @exitCode 255
 */

error_reporting(0);
require_once(__DIR__.'/../../../bootstrap.php');
error_reporting(E_ALL);
file_get_contents(__DIR__.'/does-not-exist.txt');
