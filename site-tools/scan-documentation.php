#!/usr/bin/env php
<?php

/*
 * This scans the documentation directory and add any documentation it finds to the
 * release data. Only ever commit updates from running this on the server.
 *
 * Should do more here. This doesn't detect deleted directories, it also could be
 * used in other parts of the site. Maybe this data should be stored with library
 * list, or perhaps in a separate file somewhere.
 *
 * I could possibly also automatically update the documentation list from newly
 * installed documentation.
 */

require_once(__DIR__.'/../common/code/boost.php');

function main() {
    BoostSiteTools\CommandLineOptions::parse();

    $path = realpath(STATIC_DIR);
    if (!$path || !is_dir($path)) {
        echo "Unable to find documentation directory\n";
        exit(1);
    }

    $releases = new BoostReleases(__DIR__.'/../generated/state/release.txt');

    foreach (new DirectoryIterator(STATIC_DIR) as $dir) {
        if ($dir->isDot()) { continue; }

        $name = $dir->getFilename();
        if ($name == 'develop' || $name == 'master') {
            // Store this somewhere?
        }
        else if (preg_match('@^(boost)_([0-9_]+(?:b(?:eta)?[0-9_]*)?)$@', $name, $match)) {
            $releases->addDocumentation($match[1], BoostVersion::from($match[2]), "/doc/libs/{$match[2]}/");
        }
    }

    $releases->save();
}

main();
