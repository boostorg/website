#!/usr/bin/env php
<?php

require_once(__DIR__.'/../common/code/bootstrap.php');

function main() {
    $options = BoostSiteTools\CommandLineOptions::parse(
        "Usage: {} location version");

    if (count($options->positional) != 2) {
        echo $options->usage_message();
        exit(1);
    }

    $location = $options->positional[0];
    $version = $options->positional[1];

    $libraries =
        BoostLibraries::from_xml_file(__DIR__ . '/../doc/libraries.xml');

    $unknown_libs = array();

    $maintainers = BoostMaintainers::read_from_text(
        file($location.'/libs/maintainers.txt'));

    $library_details = $libraries->get_for_version($version, null,
        'BoostLibraries::filter_all');

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
