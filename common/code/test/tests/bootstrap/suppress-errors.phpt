<?php

/* Test suppressing errors by using '@', or by setting 'error_reporting'. */

require_once(__DIR__.'/../../../bootstrap.php');

$path = __DIR__."/does-not-exist.txt";

error_reporting(-1);
assert(!@file_get_contents($path));

error_reporting(0);
assert(!file_get_contents($path));

error_reporting(E_ERROR);
assert(!file_get_contents($path));
