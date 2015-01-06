<?php

require_once(__DIR__.'/../common/code/boost.php');

if (isset($_GET['version'])) {
    try {
        $version = BoostVersion::from($_GET['version']);
    }
    catch (Exception $e) {
        // TODO: Better error, probably should be json at least
        echo "Invalid version string.";
        exit(1);
    }
} else {
    $version = BoostVersion::current();
}

// TODO: Need a better way to load the libraries.
$libs = unserialize(file_get_contents(dirname(__FILE__) . '/../generated/libraries.txt'));

// TODO: This is just crazy.
function only_released($lib) {
    return $lib['boost-version'];
}
$lib_array = $libs->get_for_version($version, null, 
    $version->is_numbered_release() ? 'only_released' : null);
$version_libs = BoostLibraries::from_array($lib_array,
    array('version' => $version));

header('Content-type: application/json');
echo $version_libs->to_json();
