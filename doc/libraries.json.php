<?php

require_once(__DIR__.'/../common/code/bootstrap.php');

if (isset($_GET['version'])) {
    try {
        $version = BoostVersion::from($_GET['version']);
    }
    catch (BoostVersion_Exception $e) {
        header('Content-type: application/json');
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Malformed request', true, 400);
        echo json_encode(Array(
            'error' => $e->getMessage(),
        ));
        exit(0);
    }
} else {
    $version = BoostVersion::current();
}

$version_libs = array_map(
    function($lib) {
        // TODO: Better handling of hidden libraries.
        if (!empty($lib['boost-version']) &&
            $lib['boost-version']->is_hidden() &&
            empty($lib['status'])
        ) {
            $lib['status'] = 'hidden';
            unset($lib['boost-version']);
        }

        $r = new BoostLibrary($lib);
        return $r;
    },
    BoostLibraries::load()->get_for_version($version, null,
        'BoostLibraries::filter_all'));

header('Content-type: application/json');
echo BoostLibrary::get_libraries_json($version_libs);
