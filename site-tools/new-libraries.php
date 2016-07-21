#!/usr/bin/env php
<?php

require_once(__DIR__.'/../common/code/boost.php');

function main() {
    $libraries = BoostLibraries::load();
    $master = $libraries->get_for_version('master');

    $unreleased_libs = array();
    foreach($master as $lib) {
        if ($lib['boost-version']->is_unreleased()) {
            $unreleased_libs[$lib['name']] = $lib;
        }
    }

    if ($unreleased_libs) {
        ksort($unreleased_libs, SORT_NATURAL | SORT_FLAG_CASE);
        $count = count($unreleased_libs);

        echo "For release notes:\n\n";

        echo "[section New Libraries]\n\n";
        foreach($unreleased_libs as $lib) {
            echo "* [phrase library..[@/{$lib['documentation']} {$lib['name']}]:]\n";
            echo "  {$lib['description']}\n\n";
        }
        echo "[endsection]\n\n";

        echo "For root index file:\n\n";

        $library_links = array();
        foreach ($unreleased_libs as $lib) {
            $library_links[] = "<a href=\"".
                filesystem_doc_link($lib).
                "\">{$lib['name']}</a>";
        }

        echo "  <p>The release includes {$count} new ".
            ($count === 1 ? "library" : "libraries").
            "\n";
        echo "  (".implode(",\n   ", $library_links)."),\n";
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
