<?php

require_once(__DIR__.'/../common/code/boost.php');

function main() {
    $args = $_SERVER['argv'];
    $boost_root = null;

    switch (count($args)) {
        case 2: $boost_root = $args[1]; break;
        default:
            echo "Usage: create-module-metadata.php boost_root\n";
            exit(1);
    }

    $library_details =
        BoostLibraries::from_xml_file(__DIR__ . '/../doc/libraries.xml')
            ->get_for_version(BoostVersion::develop());
    $super_project = new BoostSuperProject($boost_root);
    $git_submodules = $super_project->get_modules();

    // Split the libraries up into modules.

    $libraries_by_module = array();

    foreach ($library_details as $library) {
        $module = $library['module'];

        if (!isset($git_submodules[$module])) {
            echo "Unknown module: {$module}\n";
            continue;
        }

        if (isset($library['documentation'])) {
            $doc_url = $library['documentation'];
            $module_base = $git_submodules[$module]['path'];

            if ($doc_url == $module_base) {
                $doc_url = '';
            }
            else if (strpos($doc_url, "$module_base/") === 0) {
                $doc_url = substr($doc_url, strlen("$module_base/"));
            }
            else {
                $doc_url = "/$doc_url";
            }

            if (!$doc_url) {
                unset($library['documentation']);
            }
            else {
                $library['documentation'] = $doc_url;
            }
        }

        $libraries_by_module[$module][] = $library;
    }

    // Write the module metadata

    foreach ($libraries_by_module as $module => $libraries) {
        $module_libraries = BoostLibraries::from_array($libraries);
        $module_dir = "{$boost_root}/{$git_submodules[$module]['path']}";
        $meta_dir = "$module_dir/meta";
        $meta_file = "$module_dir/meta/libraries.json";

        if (!is_dir($module_dir)) {
            echo "Module '$module' doesn't exist at '$module_dir'\n";
            continue;
        }

        if (!is_dir($meta_dir)) {
            mkdir($meta_dir);
        }

        file_put_contents($meta_file, $module_libraries->to_json(
                array('boost-version', 'update-version', 'module'))."\n");
    }
}


main();
