#!/usr/bin/env php
<?php

require_once(__DIR__.'/../common/code/boost.php');

// TODO: Replace with something better.
global $quiet;
$quiet = false;

function main() {
    global $quiet;

    $args = $_SERVER['argv'];
    $location = null;
    $version = null;

    $positional_args = array();
    foreach($args as $arg) {
        if (substr($arg, 0, 2) == '--') {
            switch ($arg) {
            case '--quiet':
                $quiet = true;
                break;
            default:
                echo "Unknown flag: {$arg}\n";
                exit(1);
            }
        }
        else {
            $positional_args[] = $arg;
        }
    }

    switch (count($positional_args)) {
        case 3: $version = $positional_args[2];
        case 2: $location = $positional_args[1];
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

            $updates[(string) $version] = read_metadata_from_filesystem($location, $version);
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

            $updates[(string) $version] = read_metadata_from_filesystem($location, $version);
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

    if (!$quiet) { echo "Writing to disk\n"; }

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
    global $quiet;

    $branch = BoostVersion::from($version)->git_ref();
    if (!$quiet) { echo "Updating from {$branch}\n"; }

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
                    $updated_libs = array_merge($updated_libs, load_from_text($text, $filename, $module['path']));
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
 * @param BoostVersion $version The version of the release.
 * @throws RuntimeException
 */
function read_metadata_from_filesystem($location, $version) {
    // We don't have a list for modules, so have to work it out from the
    // existing library data.

    // Scan release for metadata files.
    $parent_directories = array("{$location}/libs");
    foreach (glob("{$location}/libs/*/sublibs") as $path) {
        $parent_directories[] = dirname($path);
    }

    // TODO: Not really a module anymore.
    $module_paths = array();
    $path_pattern = "@^{$location}/(.*)/meta/libraries.json$@";
    foreach($parent_directories as $parent) {
        foreach (glob("{$parent}/*/meta/libraries.json") as $path) {
            if (preg_match($path_pattern, $path, $match)) {
                $module_paths[] = $match[1];
            }
            else {
                echo "Unexpected path: {$path}.\n";
            }
        }
    }

    $updated_libs = array();
    foreach ($module_paths as $path) {
        $json_path = "{$location}/{$path}/meta/libraries.json";

        try {
            $updated_libs = array_merge($updated_libs, load_from_file($path, $json_path));
        } catch (library_decode_exception $e) {
            echo "Error decoding metadata for module at {$json_path}:\n{$e->content()}\n";
        }
    }

    return $updated_libs;
}

function load_from_file($path, $module_path) {
    return load_from_text(file_get_contents($path), $path, $module_path);
}

function load_from_text($text, $filename, $module_path = null) {
    $libraries = BoostLibrary::read_libraries_json($text);
    foreach($libraries as $lib) {
        $lib->set_library_path($module_path);
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
