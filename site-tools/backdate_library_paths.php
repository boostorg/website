<?php

require_once(__DIR__.'/../common/code/boost.php');

function main() {
    $location = $_SERVER['argv'][1];

    $super_project = new BoostSuperProject($location, 'develop');
    $develop_modules = $super_project->get_modules();

    $super_project2 = new BoostSuperProject($location, 'master');
    $master_modules = $super_project2->get_modules();

    $modules = array_merge($master_modules, $develop_modules);

    $libs = BoostLibraries::from_xml_file(dirname(__FILE__) . '/../doc/libraries.xml');
    $libs->update_start();
    foreach($libs->db as $library => $library_versions) {
        foreach($library_versions as $version => $library_data) {
            if (!array_key_exists('library_path', $library_data->details)) {
                if (!array_key_exists('module', $library_data->details)) {
                    echo "No module for {$library}/{$version}\n";
                }
                else {
                    $library_data->details['library_path'] =
                        $modules[$library_data->details['module']]['path'] . '/';
                    unset($library_data->details['module']);
                }
            }
        }
    }
    $libs->update_finish();
    file_put_contents(dirname(__FILE__) . '/../doc/libraries.xml', $libs->to_xml());
}

main();
