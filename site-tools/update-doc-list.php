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

    if ($location) {
        $real_location = realpath($location);

        if ($real_location && !is_dir($real_location))
        {
            echo "Not a directory: {$location}\n";
            exit(1);
        }

        $location = $real_location;

        if (get_bool_from_array(BoostSuperProject::run_process(
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

    if ($version && $version->is_numbered_release() && !$version->is_beta()) {
        $libs->update_for_release($version);
    }

    echo "Writing to disk\n";

    file_put_contents(dirname(__FILE__) . '/../doc/libraries.xml', $libs->to_xml());

    $libs->squash_name_arrays();
    file_put_contents(dirname(__FILE__) . '/../generated/libraries.txt', serialize($libs));
}

/**
 *
 * @param \BoostLibraries $libs The libraries to update.
 * @param string $location The location of the super project in the mirror.
 * @param BoostVersion|string $version The version to update from.
 * @throws RuntimeException
 */
function update_from_git($libs, $location, $version) {
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
                    $libs->update(load_from_text($text, $filename, $branch), $name, $module['path']);
                }
            }
            catch (library_decode_exception $e) {
                echo "Error decoding metadata for module {$name}:\n{$e->content()}\n";
            }
        }
    }
}

/**
 *
 * @param \BoostLibraries $libs The libraries to update.
 * @param string $location The location of the super project in the mirror.
 * @param string $branch The branch to update from.
 * @throws RuntimeException
 */
function update_from_local_copy($libs, $location, $branch = 'latest') {
    echo "Updating from local checkout/{$branch}\n";

    $super_project = new BoostSuperProject($location);
    foreach ($super_project->get_modules() as $name => $module_details) {
        foreach (
                glob("{$location}/{$module_details['path']}/meta/libraries.*")
                as $path) {
            try {
                $libs->update(load_from_file($path, $branch), $name, $module_details['path']);
            }
            catch (library_decode_exception $e) {
                echo "Error decoding metadata for module {$name}:\n{$e->content()}\n";
            }
        }
    }
}

function load_from_file($path, $branch) {
    return load_from_text(file_get_contents($path), $path, $branch);
}

function load_from_text($text, $filename, $branch) {
    $info = array();
    if ($branch) { $info['version'] = $branch; }
    switch (pathinfo($filename, PATHINFO_EXTENSION)) {
        case 'xml':
            $new_libs = BoostLibraries::from_xml($text, $info);
            break;
        case 'json':
            $new_libs = BoostLibraries::from_json($text, $info);
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
