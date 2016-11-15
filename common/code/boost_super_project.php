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
            $blob = $this->run_git("ls-tree {$this->git_branch} \"{$path}\"");
            if (!$blob || !$blob[0]) {
                return array();
            }
            $blob = preg_split("@[\t ]@", $blob[0]);
            $blob = $blob[2];

            if (self::git_version() >= array(1,8,4,0)) {
                return $this->run_git("config -l --blob {$blob}");
            }
            else {
                $temp_file = tempnam(sys_get_temp_dir(), 'boost-git-');
                file_put_contents($temp_file, implode("\n",
                    $this->run_git("show {$blob}")));
                $result = $this->run_git("config -l -f \"{$temp_file}\"");
                unlink($temp_file);
                return $result;
            }
        }
        else {
            return is_file($path) ? $this->run_git("config -l -f \"{$path}\"") : array();
        }
    }

    public function get_modules() {
        $modules = Array();

        foreach($this->parse_config_file(".gitmodules") as $line_number => $line)
        {
            if (!$line) continue;

            if (preg_match('@^submodule\.([^.=]+)\.([^.=]+)=(.*)$@i', trim($line), $matches)) {
                $modules[$matches[1]][strtolower($matches[2])] = $matches[3];
            }
            else {
                throw new BoostException("Unsupported config line: {$line}");
            }
        }

        return $modules;
    }

    public function run_git($command) {
        return self::run_process("cd \"{$this->location}\" && git {$command}");
    }

    // A couple of utility functions that don't really fit, so might move
    // later.

    static function git_version() {
        $output = self::run_process("git --version");
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

    static function run_process($command) {
        exec($command, $output, $return_var);

        if ($return_var != 0) {
            throw new ProcessError($return_var);
        }

        return $output;
    }
}

class ProcessError extends BoostException {
    public $error_code;

    function __construct($error_code) {
        $this->error_code = $error_code;
        parent::__construct("Process failed with status: {$error_code}");
    }
}
