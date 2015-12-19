#!/usr/bin/env php
<?php

require_once(__DIR__.'/../common/code/boost.php');

function main() {
    $args = $_SERVER['argv'];
    $location = null;
    $version = null;

    switch (count($args)) {
        case 3: $version = $args[2];
        case 2: $location = $args[1];
        case 1: break;
        default:
            echo "Usage: update-doc-list.php [path] [version]\n";
            exit(1);
    }

    if ($version) {
        // BoostVersion dies if version is invalid.
        $version = BoostVersion::from($version);
    }

    $libs = BoostLibraries::from_xml_file(dirname(__FILE__) . '/../doc/libraries.xml');
    $updates = array();

    if ($location) {
        $real_location = realpath($location);

        if ($real_location && !is_dir($real_location))
        {
            echo "Not a directory: {$location}\n";
            exit(1);
        }

        $location = $real_location;

        // If this is not a git repo.
        // TODO: Don't output stderr.
        exec("cd \"{$location}\" && git rev-parse --git-dir", $output, $return_var);
        if ($return_var != 0)
        {
            if (!$version || !$version->is_numbered_release()) {
                echo "Error: Release version required for release.\n";
                exit(1);
            }

            $updates[(string) $version] = read_metadata_from_release($location, $version, $libs);
        }
        else if (get_bool_from_array(BoostSuperProject::run_process(
                "cd '${location}' && git rev-parse --is-bare-repository")))
        {
            if ($version) {
                $updates[(string) $version] = read_metadata_from_git($location, $version);
            }
            else {
                $updates[(string) 'master'] = read_metadata_from_git($location, 'master');
                $updates[(string) 'develop'] = read_metadata_from_git($location, 'develop');
            }
        }
        else
        {
            // TODO: Could get version from the branch in a git checkout.
            if (!$version) {
                echo "Error: Version required for local tree.\n";
                exit(1);
            }

            $updates[(string) $version] = read_metadata_from_local_clone($location, $version);
        }
    }

    if ($updates) {
        foreach ($updates as $update_version => $update) {
            $libs->update($update_version, $update);
        }
    }
    else {
        $libs->update();
    }

    echo "Writing to disk\n";

    file_put_contents(dirname(__FILE__) . '/../doc/libraries.xml', $libs->to_xml());

    $libs->squash_name_arrays();
    file_put_contents(dirname(__FILE__) . '/../generated/libraries.txt', serialize($libs));
}

/**
 *
 * @param string $location The location of the super project in the mirror.
 * @param BoostVersion|string $version The version to update from.
 * @throws RuntimeException
 */
function read_metadata_from_git($location, $version) {
    $branch = BoostVersion::from($version)->git_ref();
    echo "Updating from {$branch}\n";

    $super_project = new BoostSuperProject($location, $branch);
    $modules = $super_project->get_modules();

    $modules_by_path = Array();
    foreach($modules as $name => $details) {
        $modules_by_path[$details['path']] = $name;
    }

    foreach($super_project->run_git(
            "ls-tree {$branch} ".implode(' ', array_keys($modules_by_path)))
        as $line_number => $line)
    {
        if (!$line) continue;

        if (preg_match("@^160000 commit ([a-zA-Z0-9]+)\t(.*)$@", $line, $matches)) {
            $modules[$modules_by_path[$matches[2]]]['hash'] = $matches[1];
        }
        else {
            throw new RuntimeException("Unmatched submodule line: {$line}");
        }
    }

    $updated_libs = array();

    foreach($modules as $name => $module) {
        $module_location = "{$location}/{$module['url']}";
        $module_command = "cd '{$module_location}' && git";

        foreach(BoostSuperProject::run_process("{$module_command} ls-tree {$module['hash']} "
                ."meta/libraries.xml meta/libraries.json") as $entry)
        {
            try {
                $entry = trim($entry);
                if (preg_match("@^100644 blob ([a-zA-Z0-9]+)\t(.*)$@", $entry, $matches)) {
                    $hash = $matches[1];
                    $filename = $matches[2];
                    $text = implode("\n", (BoostSuperProject::run_process("{$module_command} show {$hash}")));
                    $updated_libs = array_merge($updated_libs, load_from_text($text, $filename, $name, $module['path']));
                }
            }
            catch (library_decode_exception $e) {
                echo "Error decoding metadata for module {$name}:\n{$e->content()}\n";
            }
        }
    }

    return $updated_libs;
}

/**
 *
 * @param string $location The location of the super project in the mirror.
 * @param string $branch The branch to update from.
 * @throws RuntimeException
 */
function read_metadata_from_local_clone($location, $branch = 'latest') {
    echo "Updating from local checkout/{$branch}\n";

    $super_project = new BoostSuperProject($location);
    $updated_libs = array();
    foreach ($super_project->get_modules() as $name => $module_details) {
        foreach (
                glob("{$location}/{$module_details['path']}/meta/libraries.*")
                as $path) {
            try {
                $updated_libs = array_merge($updated_libs,
                    load_from_file($path, $name, $module_details['path']));
            }
            catch (library_decode_exception $e) {
                echo "Error decoding metadata for module {$name}:\n{$e->content()}\n";
            }
        }
    }
}

/**
 *
 * @param string $location The location of the super project in the mirror.
 * @param BoostVersion $version The version of the release.
 * @param \BoostLibraries $libs The existing libraries.
 * @throws RuntimeException
 */
function read_metadata_from_release($location, $version, $libs) {
    // We don't have a list for modules, so have to work it out from the
    // existing library data.

    // If we're updating an old version, then use that as the basis,
    // For a new version, take the data from the master branch, as this
    // may contain new modules that aren't in a release yet.
    $equivalent_version =
        BoostVersion::$current->compare($version) >=0 ?
            $version : BoostVersion::master();

    // Grab the modules from the metadata.
    $module_for_keys = array();
    foreach($libs->get_for_version($equivalent_version) as $details) {
        $module_for_keys[$details['key']] = $details['module'];
    }

    // Scan release for metadata files.
    $module_paths = array();
    foreach (new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator("{$location}/libs",
        FilesystemIterator::CURRENT_AS_SELF |
        FilesystemIterator::UNIX_PATHS)) as $info)
    {
        if ($info->isDot() && $info->getFilename()=='.') {
            $path = dirname($info->getSubPathname());
            if (is_file("{$info->getPathname()}/libraries.json")) {
                $module_paths[] = "libs/".dirname($path);
            }
        }
    }

    $updated_libs = array();

    foreach ($module_paths as $path) {
        $json_path = "{$location}/{$path}/meta/libraries.json";
        try {
            $libraries = BoostLibrary::read_libraries_json(
                file_get_contents($json_path));

            // Get the module for each library.
            foreach($libraries as $lib) {
                if (!isset($module_for_keys[$lib->details['key']])) {
                    echo "No module for key: {$lib->details['key']}.\n";
                } else {
                    $lib->set_module($module_for_keys[$lib->details['key']], $path);
                }
            }

            $updated_libs = array_merge($updated_libs, $libraries);
        } catch (library_decode_exception $e) {
            echo "Error decoding metadata for module at {$json_path}:\n{$e->content()}\n";
        }
    }

    return $updated_libs;
}

function load_from_file($path, $module_name, $module_path) {
    return load_from_text(file_get_contents($path), $path,
        $module_name, $module_path);
}

function load_from_text($text, $filename, $module_name = null, $module_path = null) {
    $libraries = BoostLibrary::read_libraries_json($text);
    foreach($libraries as $lib) {
        $lib->set_module($module_name, $module_path);
    }
    return $libraries;
}

function get_bool_from_array($array) {
    if (count($array) != 1) throw new RuntimeException("get_bool_from_array: invalid array");
    switch ($array[0]) {
        case 'true': return true;
        case 'false': return false;
        default: throw new RuntimeException("invalid bool: ${array[0]}");
    }
}

main();
