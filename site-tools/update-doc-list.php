<?php

require_once(dirname(__FILE__) . '/../common/code/boost_libraries.php');

function main() {
    $args = $_SERVER['argv'];
    $git_mirror = null;

    switch (count($args)) {
        case 1: break;
        case 2: $git_mirror = $args[1]; break;
        default:
            echo "Usage: update-doc-list.php [mirror_root]";
            exit(1);
    }

    $libs = boost_libraries::from_xml_file(dirname(__FILE__) . '/../doc/libraries.xml');
    $libs->squash_name_arrays();

    if ($git_mirror) {
        $git_mirror = realpath($git_mirror);
        update_from_git($libs, $git_mirror, 'master');
        update_from_git($libs, $git_mirror, 'develop');
    }

    echo "Writing to disk\n";

    file_put_contents(dirname(__FILE__) . '/../doc/libraries.xml', $libs->to_xml());
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

    $is_bare = get_bool_from_array(run_process("{$git_command} rev-parse --is-bare-repository"));

    $modules = Array();

    foreach(git_config_from_repo($git_command, $branch, ".gitmodules")
        as $line_number => $line)
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

    foreach(run_process("{$git_command} ls-tree {$branch} ".implode(' ', array_keys($modules_by_path)))
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
        $module_location = $is_bare ? "{$location}/{$module['url']}" :
            "{$location}/{$module['path']}";
        $module_command = "cd '{$module_location}' && git";

        foreach(run_process("{$module_command} ls-tree {$module['hash']} "
                ."meta/libraries.xml meta/libraries.json") as $entry)
        {
            $entry = trim($entry);
            if (preg_match("@^100644 blob ([a-zA-Z0-9]+)\t(.*)$@", $entry, $matches)) {
                $hash = $matches[1];
                $filename = $matches[2];
                $text = implode("\n", (run_process("{$module_command} show {$hash}")));
                switch (pathinfo($filename, PATHINFO_EXTENSION)) {
                    case 'xml':
                        $new_libs = boost_libraries::from_xml($text);
                        break;
                    case 'json':
                        $new_libs = boost_libraries::from_json($text);
                        break;
                    default:
                        assert(false);
                }

                $new_libs->squash_name_arrays();
                $libs->update($new_libs, $branch, $module);
            }
        }
    }
}

function git_config_from_repo($git_command, $branch, $path) {
    $temp_file = null;

    if (git_version($git_command) >= array(1,8,4,0))
    {
        $blob = run_process("{$git_command} ls-tree {$branch} .gitmodules "
            ."| cut -f 1 | cut -f 3 -d ' '");
        $file_param = "--blob {$blob[0]}";
    }
    else
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'boost-git-');
        run_process("{$git_command} show {$branch}:{$path} ".
                "> {$temp_file}");
        $file_param = "-f {$temp_file}";
    }

    $result = run_process("{$git_command} config -l {$file_param}");
    if ($temp_file) unlink($temp_file);
    return $result;
}

function git_version($git_command) {
    $output = run_process("{$git_command} --version");
    $match = null;

    if (count($output) == 1
            && preg_match('@^git version ([0-9.]+)$@', $output[0], $match))
    {
        return array_pad(explode('.', $output[0]), 4, 0);
    }
    else {
        return array(0,0,0,0);
    }
}

function get_bool_from_array($array) {
    if (count($array) != 1) throw new RuntimeException("get_bool_from_array: invalid array");
    switch ($array[0]) {
        case 'true': return true;
        case 'false': return false;
        default: throw new RuntimeException("invalid bool: ${array[0]}");
    }
}

class ProcessError extends RuntimeException {
    public $error_code;

    function __construct($error_code) {
        $this->error_code = $error_code;
        parent::__construct("Process failed with status: {$error_code}");
    }
}

function run_process($command) {
    exec($command, $output, $return_var);

    if ($return_var != 0) {
        throw new ProcessError($return_var);
    }

    return $output;
}

main();
