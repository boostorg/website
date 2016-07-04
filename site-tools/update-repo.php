#!/usr/bin/env php
<?php

require_once(__DIR__.'/../common/code/boost.php');

function main() {
    $args = $_SERVER['argv'];

    if (count($args) != 3) {
        echo "Usage: update-maintainers.php location version\n";
        exit(1);
    }

    $location = $args[1];
    $version = $args[2];

    $libraries =
        BoostLibraries::from_xml_file(__DIR__ . '/../doc/libraries.xml');

    $unknown_libs = array();

    $maintainers = BoostMaintainers::read_from_text(
        file($location.'/libs/maintainers.txt'));

    // TODO: Want to include hidden libraries here.
    $library_details = $libraries->get_for_version($version, null);

    $libs_index = array();
    foreach ($library_details as $index => $details) {
        if (isset($details['maintainers'])) {
            $maintainers->update_maintainer($details['key'],
                $details['maintainers']);
        }
    }

    file_put_contents($location.'/libs/maintainers.txt',
        $maintainers->write_to_text());
}


main();
