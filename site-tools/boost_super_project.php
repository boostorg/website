<?php

class BoostSuperProject {
    /** Directory containing the super project */
    var $location;

    /** The git branch to use. False to use filesystem. */
    var $git_branch;

    function __construct($location, $git_branch = false) {
        $this->location = $location;
        $this->git_branch = $git_branch;
    }

    public function parse_config_file($path) {
        if ($this->git_branch) {
            if (git_version() >= array(1,8,4,0)) {
                $blob = $this->run_git("ls-tree {$this->git_branch} \"{$path}\"");
                $blob = preg_split("@[\t ]@", $blob[0])[2];
                return $this->run_git("config -l --blob {$blob}");
            }
            else {
                $temp_file = tempnam(sys_get_temp_dir(), 'boost-git-');
                file_put_contents($temp_file, implode("\n",
                    $this->run_git("show \"{$this->git_branch}:{$path}\"")));
                $result = $this->run_git("config -l -f \"{$temp_file}\"");
                unlink($temp_file);
                return $result;
            }
        }
        else {
            return $this->run_git("config -l -f \"{$path}\"");
        }
    }

    public function run_git($command) {
        return run_process("git -C \"{$this->location}\" {$command}");
    }
}

function git_version() {
    $output = run_process("git --version");
    $match = null;

    if (count($output) == 1
            && preg_match('@^git version ([0-9.]+)$@', $output[0], $match))
    {
        return array_pad(explode('.', $match[1]), 4, 0);
    }
    else {
        return array(0,0,0,0);
    }
}

function run_process($command) {
    exec($command, $output, $return_var);

    if ($return_var != 0) {
        throw new ProcessError($return_var);
    }

    return $output;
}

class ProcessError extends RuntimeException {
    public $error_code;

    function __construct($error_code) {
        $this->error_code = $error_code;
        parent::__construct("Process failed with status: {$error_code}");
    }
}
