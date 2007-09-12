<?php
require_once(dirname(__FILE__) . '/../common/code/archive_file.php');

$_file = new archive_file('/^[\/]([^\/]+)[\/](.*)$/',$_SERVER["PATH_INFO"],false,RESULTS_DIR."/","");
?>
