<?php

require_once(dirname(__FILE__) . '/../common/code/boost_libraries.php');
require_once(dirname(__FILE__) . '/boost_super_project.php');

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

    // TODO: Support releases.
    if ($version && !in_array($version, ['master', 'develop', 'latest'])) {
        echo "Invalid version: $version\n";
        exit(1);
    }

    $libs = boost_libraries::from_xml_file(dirname(__FILE__) . '/../doc/libraries.xml');

    if ($location) {
        $location = realpath($location);

        if (is_file($location))
        {
            update_from_file($libs, $location, $version);
        }
        else if (get_bool_from_array(run_process(
                "cd '${location}' && git rev-parse --is-bare-repository")))
        {
            if ($version) {
                update_from_git($libs, $location, $version);
            }
            else {
                update_from_git($libs, $location, 'master');
                update_from_git($libs, $location, 'develop');
            }
        }
        else
        {
            // TODO: Could get version from the branch in a git checkout.
            // TODO: Support non-git trees (i.e. a release).
            if (!$version) {
                echo "Error: Version required for local tree.\n";
                exit(1);
            }

            update_from_local_copy($libs, $location, $version);
        }
    }

    echo "Writing to disk\n";

    file_put_contents(dirname(__FILE__) . '/../doc/libraries.xml', $libs->to_xml());

    $libs->squash_name_arrays();
    file_put_contents(dirname(__FILE__) . '/../generated/libraries.txt', serialize($libs));
}

/**
 *
 * @param \boost_libraries $libs The libraries to update.
 * @param string $location The location of the super project in the mirror.
 * @param string $branch The branch to update from.
 * @throws RuntimeException
 */
function update_from_git($libs, $location, $branch) {
    echo "Updating from {$branch}\n";

    $git_command = "cd '${location}' && git";
    $super_project = new BoostSuperProject($location, $branch);
    $modules = Array();

    foreach($super_project->parse_config_file(".gitmodules") as $line_number => $line)
    {
        if (!$line) continue;

        if (preg_match('@^submodule\.(\w+)\.(\w+)=(.*)$@', trim($line), $matches)) {
            $modules[$matches[1]][$matches[2]] = $matches[3];
        }
        else {
            throw new RuntimeException("Unsupported config line: {$line}");
        }
    }

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

    foreach($modules as $name => $module) {
        $module_location = "{$location}/{$module['url']}";
        $module_command = "cd '{$module_location}' && git";

        foreach(run_process("{$module_command} ls-tree {$module['hash']} "
                ."meta/libraries.xml meta/libraries.json") as $entry)
        {
            $entry = trim($entry);
            if (preg_match("@^100644 blob ([a-zA-Z0-9]+)\t(.*)$@", $entry, $matches)) {
                $hash = $matches[1];
                $filename = $matches[2];
                $text = implode("\n", (run_process("{$module_command} show {$hash}")));
                $libs->update(load_from_text($text, $filename, $branch), $name);
            }
        }
    }
}

/**
 *
 * @param \boost_libraries $libs The libraries to update.
 * @param string $location The location of the super project in the mirror.
 * @param string $branch The branch to update from.
 * @throws RuntimeException
 */
function update_from_local_copy($libs, $location, $branch = 'latest') {
    echo "Updating from local checkout/{$branch}\n";

    foreach (glob("{$location}/libs/*") as $module_path) {
        foreach (glob("{$module_path}/meta/libraries.*") as $path) {
            // TODO: Would be better to get module names from .gitmodules file.
            $module = pathinfo($module_path, PATHINFO_FILENAME);
            $libs->update(load_from_file($path, $branch), $module);
        }
    }
}

function update_from_file($libs, $location, $version) {
    echo "Updated from local file\n";
    $libs->update(load_from_file($location, $version));
}

function load_from_file($path, $branch) {
    return load_from_text(file_get_contents($path), $path, $branch);
}

function load_from_text($text, $filename, $branch) {
    switch (pathinfo($filename, PATHINFO_EXTENSION)) {
        case 'xml':
            $new_libs = boost_libraries::from_xml($text, $branch);
            break;
        case 'json':
            $new_libs = boost_libraries::from_json($text, $branch);
            break;
        default:
            echo "Error: $filename.\n"; exit(0);
            assert(false);
    }

    return $new_libs;
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
