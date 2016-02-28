#!/usr/bin/env php
<?php

require_once(__DIR__.'/../common/code/boost.php');

function main() {
    $libraries = BoostLibraries::load();
    $master = $libraries->get_for_version('master');

    $unreleased_libs = [];
    foreach($master as $lib) {
        if ($lib['boost-version']->is_unreleased()) {
            $unreleased_libs[$lib['name']] = $lib;
        }
    }

    if ($unreleased_libs) {
        ksort($unreleased_libs, SORT_NATURAL | SORT_FLAG_CASE);

        echo "[section New Libraries]\n\n";
        foreach($unreleased_libs as $lib) {
            echo "* [phrase library..[@{$lib['documentation']} {$lib['name']}]:]\n";
            echo "  {$lib['description']}\n\n";
        }
    }
}

main();
