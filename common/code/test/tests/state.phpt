<?php

use Tester\Assert;

require_once(__DIR__.'/../config/bootstrap.php');
require_once(__DIR__.'/../../boost.php');

function normalise($text) {
    $text = preg_replace('@^ *@m', '', $text);
    $text = trim($text);
    if ($text) { $text .= "\n"; }
    return $text;
}

function nested_key_shuffle($x) {
    $keys = array_keys($x);
    shuffle($keys);

    $shuffled = array();
    foreach($keys as $key) {
        $value = $x[$key];
        $shuffled[$key] =
            is_array($value) ? nested_key_shuffle($value) : $value;
    }

    return $shuffled;
}

function test_state_file($state, $text) {
    $text = normalise($text);

    $tmp_file = tempnam(sys_get_temp_dir(), 'boost-website-tests-');
    file_put_contents($tmp_file, $text);
    Assert::same($state, BoostState::load($tmp_file));

    BoostState::save($state, $tmp_file);
    Assert::same($text, file_get_contents($tmp_file));

    // Check that the order of the keys is correct in saved file.
    BoostState::save(nested_key_shuffle($state), $tmp_file);
    Assert::same($text, file_get_contents($tmp_file));

    BoostState::save_json($state, $tmp_file);
    Assert::same($state, BoostState::load_json($tmp_file));

    unlink($tmp_file);
}

test_state_file(array(), '');
test_state_file(
    array(
        'empty0' => array(),
        'empty1' => array(),
        'empty2' => array(),
        'empty3' => array(),
        'empty4' => array(),
    ), '
        (empty0
        )
        (empty1
        )
        (empty2
        )
        (empty3
        )
        (empty4
        )
    ');
test_state_file(
    array(
        'foo' => array(
            'block' => "Line one\nLine 2",
            'bool_false' => false,
            'bool_true' => true,
            'empty' => null,
            'num_float' => 2.0,
            'num_int' => 1,
            'zzz' => null,
        ),
    ), '
        (foo
        -block
        "Line one
        "Line 2
        -bool_false
        !0
        -bool_true
        !1
        -empty
        -num_float
        .2
        -num_int
        =1
        -zzz
        )
    ');
