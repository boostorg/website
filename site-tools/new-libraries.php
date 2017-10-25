#!/usr/bin/env php
<?php

require_once(__DIR__.'/../common/code/bootstrap.php');

define('NEW_LIBRARIES_USAGE', "
Usage: {}

Writes out new library information for the release notes.
");

function main() {
    BoostSiteTools\CommandLineOptions::parse(NEW_LIBRARIES_USAGE);

    $libraries = BoostLibraries::load();
    $libs = $libraries->get_for_version('master', null, function($lib) {
        return !$lib['boost-version']->is_final_release() &&
            !$lib['boost-version']->is_hidden() &&
            BoostWebsite::array_get($lib, 'status') != 'hidden' &&
            BoostWebsite::array_get($lib, 'status') != 'unreleased';
    });

    $unreleased_libs = array();
    foreach($libs as $lib) {
        $unreleased_libs[$lib['name']] = $lib;
    }

    if ($unreleased_libs) {
        ksort($unreleased_libs, SORT_NATURAL | SORT_FLAG_CASE);
        $count = count($unreleased_libs);

        echo "For release notes:\n\n";

        echo "[section New Libraries]\n\n";
        foreach($unreleased_libs as $lib) {
            $authors = is_array($lib['authors']) ? implode(', ', $lib['authors']) : $lib['authors'];
            $description = preg_replace('@(?<![.\s])[.\s]*$@', '', $lib['description']);
            echo "* [phrase library..[@/{$lib['documentation']} {$lib['name']}]:]\n";
            echo "  {$description}, from {$authors}.\n\n";
        }
        echo "[endsect]\n\n";
    }
    else {
        echo "No new libraries yet.\n";
    }
}

function filesystem_doc_link($lib) {
    $link = $lib['documentation'];
    if (substr($link, -1) === '/') {
        $link .= 'index.html';
    }
    return $link;
}

main();
