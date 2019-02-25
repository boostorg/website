#!/usr/bin/env php
<?php

require_once(__DIR__.'/../common/code/bootstrap.php');

define ('UPDATE_DOC_LIST_USAGE', "
Usage: {} [path] [version]

Options:

    --quiet

Updates the library metadata in the documentation list.

The path argument can be either a boost release, or the path of the
boost super project in a full mirror of the git repositories.

The version argument is the version of boost to update for. If missing
will update master and develop from a git mirror.

When called with no arguments, just updates the serialized cache.
Used for manual updates.

Example
=======

To update from a beta release:

    {} boost_1_62_0_b1 1.62.0.beta1
");

class UpdateDocListSettings {
    static $quiet = false;
}

function main() {
    $options = BoostSiteTools\CommandLineOptions::parse(
        UPDATE_DOC_LIST_USAGE, array('quiet' => false));

    UpdateDocListSettings::$quiet = $options->flags['quiet'];
    $location = null;
    $version = null;

    switch (count($options->positional)) {
        case 2: $version = $options->positional[1];
        case 1: $location = $options->positional[0];
        case 0: break;
        default:
            echo $options->usage_message();
            exit(1);
    }

    if ($version) {
        // BoostVersion throws an exception if version is invalid.
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

        // TODO: Don't output stderr.
        exec("cd \"{$location}\" && git rev-parse --git-dir", $output, $return_var);
        if ($return_var != 0)
        {
            // If this is not a git repo.

            if (!$version || !$version->is_numbered_release()) {
                echo "Error: Release version required for release.\n";
                exit(1);
            }

            $updates[(string) $version] = read_metadata_from_filesystem($location, $version);
        }
        else if (get_bool_from_array(BoostSuperProject::run_process(
                "cd '${location}' && git rev-parse --is-bare-repository")))
        {
            // If this is a bare repository, assume it's part of a mirror.

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
            // Otherwise, it's a local git clone. I'm not sure is this is
            // a valid use of the script.
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

    if (!UpdateDocListSettings::$quiet) { echo "Writing to disk\n"; }

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
    if (!UpdateDocListSettings::$quiet) { echo "Updating from {$branch}\n"; }
    return read_metadata_from_modules('', $location, $branch);
}

function read_metadata_from_modules($path, $location, $hash, $sublibs = array('libs' => true)) {
    // echo "Reading from {$path} - {$location} - {$hash}.\n";

    $super_project = new BoostSuperProject($location, $hash);
    $modules = $super_project->get_modules();

    // Used to quickly set submodule hash.
    $modules_by_path = Array();
    foreach($modules as $name => $details) {
        $modules_by_path[$details['path']] = $name;
    }

    // Store possible metadata files in this array.
    $metadata_files = array();

    // Get a list of everything that's relevant in the superproject+modules.
    foreach($super_project->run_git("ls-tree {$hash} -r") as $line_number => $line)
    {
        if (!$line) continue;

        if (preg_match("@^(\d{6}) (\w+) ([a-zA-Z0-9]+)\t(.*)$@", $line, $matches)) {
            switch($matches[2]) {
            case 'blob':
                $blob_path = $path ? "{$path}/$matches[4]" : $matches[4];

                if (fnmatch('*/sublibs', $blob_path)) {
                    $sublibs[dirname($blob_path)] = true;
                }
                else if (fnmatch('*/meta/libraries.json', $blob_path)) {
                    $metadata_files[$blob_path] = $matches[3];
                }
                break;
            case 'commit':
                $modules[$modules_by_path[$matches[4]]]['hash'] = $matches[3];
                break;
            }
        }
        else {
            throw new RuntimeException("Unmatched submodule line: {$line}");
        }
    }

    // Process metadata files
    $updated_libs = array();
    foreach ($metadata_files as $metadata_path => $metadata_hash) {
        if (empty($sublibs[dirname(dirname(dirname($metadata_path)))])) {
            echo "Ignoring non-library metadata file: {$metadata_path}.\n";
        }
        else {
            $text = implode("\n", $super_project->run_git("show {$metadata_hash}"));
            try {
                $updated_libs = array_merge($updated_libs, load_from_text($text, $metadata_path, dirname(dirname($metadata_path))));
            } catch (BoostLibraries_DecodeException $e) {
                echo "Error decoding metadata for library at {$metadata_path}:\n{$e->content()}\n";
            }
        }
    }

    // Recurse over submodules
    foreach($modules as $name => $module) {
        $submodule_path = $path ? "{$path}/{$module['path']}" : $module['path'];

        if (!preg_match('@^\.\./(\w+)(\.git)?$@', $module['url'])) {
            // In quiet mode don't warn about documentation submodules, which
            // libraries have previously included from remote locations.
            if (!UpdateDocListSettings::$quiet || strpos($submodule_path.'/', '/doc/') === false) {
                echo "Ignoring submodule '{$name}' in '{$location}'.\n";
            }
            continue;
        }

        if (empty($module['hash'])) {
            echo "Missing module in .gitmodule: '{$name}' in '{$location}'.\n";
            continue;
        }

        $updated_libs = array_merge($updated_libs, read_metadata_from_modules(
            $submodule_path,
            "{$location}/{$module['url']}",
            $module['hash'],
            $sublibs));
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
    // Scan release for metadata files.
    $parent_directories = array("{$location}/libs");
    foreach (glob("{$location}/libs/*/sublibs") as $path) {
        $parent_directories[] = dirname($path);
    }

    $library_paths = array();
    $path_pattern = "@^{$location}/(.*)/meta/libraries.json$@";
    foreach($parent_directories as $parent) {
        foreach (glob("{$parent}/*/meta/libraries.json") as $path) {
            if (preg_match($path_pattern, $path, $match)) {
                $library_paths[] = $match[1];
            }
            else {
                echo "Unexpected path: {$path}.\n";
            }
        }
    }

    $updated_libs = array();
    foreach ($library_paths as $path) {
        $json_path = "{$location}/{$path}/meta/libraries.json";

        try {
            $updated_libs = array_merge($updated_libs, load_from_file($json_path, $path));
        } catch (BoostLibraries_DecodeException $e) {
            echo "Error decoding metadata for library at {$json_path}:\n{$e->content()}\n";
        }
    }

    return $updated_libs;
}

function load_from_file($path, $library_path) {
    return load_from_text(file_get_contents($path), $path, $library_path);
}

function load_from_text($text, $filename, $library_path = null) {
    $libraries = BoostLibrary::read_libraries_json($text);
    foreach($libraries as $lib) {
        $lib->set_library_path($library_path);
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
