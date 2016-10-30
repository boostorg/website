#!/usr/bin/env php
<?php

define('SET_RELEASE_STATUS_USAGE', "
Usage: {} version

Used to mark a version a released.

Example:

{} 1.63.0.beta1
{} 1.63.0
");

require_once(__DIR__.'/../common/code/bootstrap.php');

function main() {
    $options = BoostSiteTools\CommandLineOptions::parse(
        SET_RELEASE_STATUS_USAGE);

    if (!count($options->positional)) {
        echo $options->usage_message();
        exit(1);
    }

    $version = BoostVersion::from($options->positional[0]);

    $releases = new BoostReleases(__DIR__.'/../generated/state/release.txt');
    $releases->setReleaseStatus($version, 'released');
    $releases->save();
}

main();
