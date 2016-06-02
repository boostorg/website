<?php

use Tester\Assert;

require_once(__DIR__.'/../config/bootstrap.php');
require_once(__DIR__.'/../../boost.php');

function run_tests() {
    foreach(glob(__DIR__."/../vendor/mustache/spec/specs/*.json") as $test_path) {
        $test_name = pathinfo($test_path, PATHINFO_FILENAME);
        $test_cases = json_decode(file_get_contents($test_path));

        if ($test_name[0] === '~') {
            echo "*** Ignoring optional test: {$test_name}\n";
        }
        else {
            echo "*** Running tests from: {$test_name}\n";

            foreach($test_cases->tests as $test_case) {
                echo "{$test_case->name}\n";
                if (property_exists($test_case, 'partials')) {
                    Assert::same($test_case->expected,
                        BoostSimpleTemplate::render(
                            $test_case->template, (array) $test_case->data,
                            (array) $test_case->partials));
                }
                else {
                    Assert::same($test_case->expected,
                        BoostSimpleTemplate::render(
                            $test_case->template, (array) $test_case->data));
                }
            }
        }
    }
}

function indentation_tests() {
    Assert::same("  ",
        BoostSimpleTemplate::render(
            "  {{> partial}}",
            array(),
            array('partial' => "")));

    Assert::same("   ",
        BoostSimpleTemplate::render(
            "  {{> partial}}",
            array(),
            array('partial' => " ")));

    Assert::same("  ",
        BoostSimpleTemplate::render(
            "  {{> partial}}",
            array(),
            array('partial' => "{{data}}")));

    Assert::same("  Before\n  12\n  After\n",
        BoostSimpleTemplate::render(
            "  {{> partial}}\n",
            array('list' => array(1,2)),
            array('partial' => "Before\n{{#list}}{{.}}{{/list}}\nAfter\n")));

    Assert::same("  Before\n    12\n  After\n",
        BoostSimpleTemplate::render(
            "  {{> partial}}\n",
            array('list' => array(1,2)),
            array('partial' => "Before\n  {{#list}}{{.}}{{/list}}\nAfter\n")));

    Assert::same("  Before\n  1\n  2\n  After\n",
        BoostSimpleTemplate::render(
            "  {{> partial}}\n",
            array('list' => array(1,2)),
            array('partial' => "Before\n{{#list}}\n{{.}}\n{{/list}}\nAfter\n")));

    Assert::same("  Before\n  1\n2\n  After\n",
        BoostSimpleTemplate::render(
            "  {{> partial}}\n",
            array('list' => array(1,2)),
            array('partial' => "Before\n{{#list}}{{.}}\n{{/list}}\nAfter\n")));

    Assert::same("  Before\n  1  2\n  After\n",
        BoostSimpleTemplate::render(
            "  {{> partial}}\n",
            array('list' => array(1,2)),
            array('partial' => "Before\n{{#list}}\n{{.}}{{/list}}\nAfter\n")));

    Assert::same("  Before\n  (\n  1\n\n  2\n)\n  After\n",
        BoostSimpleTemplate::render(
            "  {{> partial}}\n",
            array('list' => array(1,2)),
            array('partial' => "Before\n({{#list}}\n{{.}}\n{{/list}})\nAfter\n")));

    Assert::same("  Before\n  (\n  1\n\n  2\n  After\n",
        BoostSimpleTemplate::render(
            "  {{> partial}}\n",
            array('list' => array(1,2)),
            array('partial' => "Before\n({{#list}}\n{{.}}\n{{/list}}\nAfter\n")));

    Assert::same("  Before\n  1\n  2\n)\n  After\n",
        BoostSimpleTemplate::render(
            "  {{> partial}}\n",
            array('list' => array(1,2)),
            array('partial' => "Before\n{{#list}}\n{{.}}\n{{/list}})\nAfter\n")));

    Assert::same("a/b/c/3",
        BoostSimpleTemplate::render(
            "{{>a/1}}",
            array(),
            array(
                'a/1' => '{{>b/c/d/2}}',
                'a/b/c/d/2' => 'a/b/c/3',//'{{>../3}}',
                'a/b/c/3' => 'a/b/c/3',
            )
        ));
}

run_tests();
indentation_tests();
