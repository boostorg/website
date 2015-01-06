<?php

require_once(__DIR__.'/../common/code/boost.php');

// TODO: Correct http headers.

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
    $version = BoostVersion::from('develop'); //current();
}

// TODO: Need a better way to load the libraries.
$libs = unserialize(file_get_contents(dirname(__FILE__) . '/../generated/libraries.txt'));

// TODO: This is just crazy.
$lib_array = $libs->get_for_version($version, null, function($lib) use($version) {
    return !$version->is_numbered_release() || $lib['boost-version'];
});
$version_libs = BoostLibraries::from_array($lib_array,
    array('version' => $version));

echo $version_libs->to_json();
