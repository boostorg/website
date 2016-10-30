<?php

namespace BoostSiteTools;

class CommandLineOptions
{
    var $script;
    var $usage_message;

    var $positional = array();
    var $flags = array();

    function usage_message() {
        $message =
            str_replace('{}', basename($this->script), $this->usage_message);
        $message = preg_replace('@^\s*\n@', '', $message); // Trim leading blank lines.
        $message = rtrim($message);
        return $message."\n";
    }

    /* $usage_message - Usage message for incorrect arguments or --help
     *                  '{}' will be replaced with name of script.
     * $flags - Array mapping accepted option names to default values.
     * $argv - Command line arguments to parse, defaults to $_SERVER['argv']
     *         $argv[0] is the name of the script.
     *
     * Exits if parse error, or for --help flag
     * Returns CommandLineOptions
     */
    static function parse($usage_message = 'Usage: {}', $flags = null, $argv = null) {
        if (is_null($argv)) { $argv = $_SERVER['argv']; }
        if (is_null($flags)) { $flags = array(); }
        $flags += array('help' => false);

        $options = new self();
        $options->script = array_shift($argv);
        $options->usage_message = $usage_message;
        $options->flags = $flags;

        $usage_error = false;

        foreach ($argv as $arg) {
            if (preg_match('@^--([^=]*)(=(.*))?$@', $arg, $match)) {
                if (!array_key_exists($match[1], $flags)) {
                    echo "Unknown flag: {$match[1]}.\n";
                    $usage_error = true;
                }
                else {
                    $options->flags[$match[1]] =
                        !empty($match[2]) ? $match[3] : true;
                }
            }
            else {
                $options->positional[] = $arg;
            }
        }

        if ($usage_error) {
            echo "\n".$options->usage_message();
            exit(1);
        }

        if ($options->flags['help']) {
            echo $options->usage_message();
            exit(0);
        }

        return $options;
    }
}
