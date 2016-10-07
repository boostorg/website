#!/usr/bin/env php
<?php

require_once(__DIR__.'/../common/code/boost.php');

function main() {
    $args = $_SERVER['argv'];

    if (count ($args) != 2) {
        echo "Usage: load-release-data.php path\n";
        exit(1);
    }

    $path = realpath($args[1]);
    if (!$path) {
        echo "Unable to find release file: {$args[1]}\n";
        exit(1);
    }

    $release_details = file_get_contents($path);
    if (!$release_details) {
        echo "Error reading release file: {$args[1]}\n";
        exit(1);
    }

    $releases = new BoostReleases(__DIR__.'/../generated/state/release.txt');
    $releases->loadReleaseInfo($release_details);
    $releases->save();
}

main();
