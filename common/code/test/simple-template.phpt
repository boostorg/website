<?php

use Tester\Assert;

require_once(__DIR__.'/config/bootstrap.php');
require_once(__DIR__.'/../boost.php');

function run_tests() {
    foreach(glob(__DIR__."/vendor/mustache/spec/specs/*.json") as $test_path) {
        $test_name = pathinfo($test_path, PATHINFO_FILENAME);
        $test_cases = json_decode(file_get_contents($test_path));

        if ($test_name[0] === '~') {
            echo "*** Ignoring optional test: {$test_name}\n";
        }
        else if (in_array($test_name, ['delimiters', 'partials'])) {
            echo "*** Checking for failure in supported test: {$test_name}\n";

            foreach($test_cases->tests as $test_case) {
                echo "{$test_case->name}\n";
                Assert::Exception(function() use($test_case) {
                    BoostSimpleTemplate::render_to_string($test_case->template, (array) $test_case->data);
                }, 'BoostSimpleTemplateException');
            }
        }
        else {
            echo "*** Running tests from: {$test_name}\n";

            foreach($test_cases->tests as $test_case) {
                echo "{$test_case->name}\n";
                Assert::same($test_case->expected,
                    BoostSimpleTemplate::render_to_string(
                        $test_case->template, (array) $test_case->data));
            }
        }
    }
}

run_tests();
