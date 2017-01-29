<?php

/**
 * Check that the error reporter catches a fatal error
 *
 * @httpCode 500
 * @exitCode 255
 */

// Note: Turn off all error reporting as it is printed before the error code
//       when run as CGI.
error_reporting(0);

require_once(__DIR__.'/../../../bootstrap.php');
require_once(__DIR__.'/syntax-error.php');
