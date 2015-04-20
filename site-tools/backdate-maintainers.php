<?php

require_once(__DIR__.'/../common/code/boost.php');

function main() {
    $args = $_SERVER['argv'];
    $location = null;

    switch (count($args)) {
        case 2: $location = $args[1]; break;
        default:
            echo "Usage: backdate-maintainers.php location\n";
            exit(1);
    }

    // Get the library data, so that it can be updated with maintainers.
    // In case you're wondering why the result from get_for_version doesn't
    // use 'key' as its key, it's for historical reasons I think, might be
    // fixable.

    $libraries =
        BoostLibraries::from_xml_file(__DIR__ . '/../doc/libraries.xml');

    $unknown_libs = array();

    foreach (BoostSuperProject::run_process("git -C {$location} tag") as $tag) {
        if (preg_match('@^boost-1\.\d+\.\d+$@', $tag)) {
            $library_details = $libraries->get_for_version($tag, null,
                'BoostLibraries::filter_all');

            $libs_index = array();
            foreach ($library_details as $index => $details) {
                $libs_index[$details['key']] = $index;
            }

            foreach(BoostMaintainers::read_from_text(
                git_file($location, $tag, 'libs/maintainers.txt'))->maintainers
                as $key => $lib_maintainers)
            {
                if (isset($libs_index[$key])) {
                    $index = $libs_index[$key];
                    $library_details[$index]['maintainers'] =
                        $lib_maintainers;
                }
                else {
                    $unknown_libs[$key][] = $tag;
                }
            }

            $update = array_map(function($lib) {
                return new BoostLibrary($lib);
            }, $library_details);

            $libraries->update($update, $tag);
        }
    }

    file_put_contents(dirname(__FILE__) . '/../doc/libraries.xml',
        $libraries->to_xml());

    $names = array_keys($unknown_libs);
    sort($names);
    echo "Unable to find libraries:\n";
    foreach ($names as $lib) {
        echo "{$lib} from: ".implode(', ', $unknown_libs[$lib])."\n";
    }
}

function git_file($location, $ref, $path) {
    foreach(BoostSuperProject::run_process(
        "git -C {$location} ls-tree {$ref} {$path}") as $entry)
    {
        if (!preg_match("@^100644 blob ([a-zA-Z0-9]+)\t(.*)$@",
            $entry, $matches)) { assert(false); }
        return BoostSuperProject::run_process(
            "git -C {$location} show {$matches[1]}");
    }
    return false;
}

main();
