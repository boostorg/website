<?php

/**
 * Check that the error reporter catches a fatal error
 *
 * @httpCode 500
 * @exitCode 255
 * This test is failling on php 5.3 in CGI mode, probably because PHP is
 * writing out an error before the HTTP header.  I can't see any way to avoid
 * that.
 * @phpVersion >= 5.4
 */

// Note: Turn off all error reporting as it is printed before the error code
//       when run as CGI.
error_reporting(0);

require_once(__DIR__.'/../../../bootstrap.php');
require_once(__DIR__.'/syntax-error.php');
