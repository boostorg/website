<?php

require_once(__DIR__.'/../common/code/boost.php');

if (isset($_GET['version'])) {
    try {
        $version = BoostVersion::from($_GET['version']);
    }
    catch (BoostVersion_Exception $e) {
        echo json_encode(Array(
            'error' => $e->getMessage(),
        ));
        exit(0);
    }
} else {
    $version = BoostVersion::current();
}

// TODO: This is a bit awkard, should probably have an alternative
//       to 'get_for_version' which returns a BoostLibraries instance
//       rather than an array.
$version_libs = BoostLibraries::from_array(
    BoostLibraries::load()->get_for_version($version),
    array('version' => $version));

header('Content-type: application/json');
echo $version_libs->to_json();
