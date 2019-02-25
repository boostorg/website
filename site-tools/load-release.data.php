#!/usr/bin/env php
<?php

define ('LOAD_RELEASE_DATA_USAGE', "
Usage: {} path

Loads the release data from the file specified by 'path'.

File format is:

    Download page URL [version]

    Output of sha256sum

For example:

https://sourceforge.net/projects/boost/files/boost/1.62.0/

b91c2cda8bee73ea613130e19e72c9589e9ef0357c4c5cc5f7523de82cce11f7  boost_1_62_0.7z
36c96b0f6155c98404091d8ceb48319a28279ca0333fba1ad8611eb90afb2ca0  boost_1_62_0.tar.bz2
440a59f8bc4023dbe6285c9998b0f7fa288468b889746b1ef00e8b36c559dce1  boost_1_62_0.tar.gz
084b2e0638bbe0975a9e43e21bc9ceae33ef11377aecab3268a57cf41e405d4e  boost_1_62_0.zip
");

require_once(__DIR__.'/../common/code/bootstrap.php');

function main() {
    $options = BoostSiteTools\CommandLineOptions::parse(
        LOAD_RELEASE_DATA_USAGE);

    $path = null;
    $version = null;

    switch (count ($options->positional)) {
    case 2:
        $version = $options->positional[1];
        // Fallthrough
    case 1: 
        $path = $options->positional[0];
        break;
    default:
        echo $options->usage_message();
        exit(1);
    }

    $realpath = realpath($path);
    if (!$realpath) {
        echo "Unable to find release file: {$path}\n";
        exit(1);
    }

    $release_details = file_get_contents($realpath);
    if (!$release_details) {
        echo "Error reading release file: {$path}\n";
        exit(1);
    }

    $releases = new BoostReleases(__DIR__.'/../generated/state/release.txt');
    $releases->loadReleaseInfo($release_details, 'boost', $version);
    $releases->save();
}

main();
