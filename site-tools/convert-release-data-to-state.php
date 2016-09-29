<?php

require_once(__DIR__.'/../common/code/boost.php');

// Convert the release json date to a state file.
//
// This is pretty awkward as state files don't support nested data, so
// I've improvised a key naming scheme to handle it.

function main() {
    $release_data = json_decode(file_get_contents(__DIR__.'/../feed/history/releases.json'), true);

    $release_state = array();
    foreach ($release_data as $release => $details) {
        $release_state[$release] = flatten_array($details);
    }

    BoostState::save($release_state, __DIR__.'/../generated/state/release.txt');
}

function flatten_array($x, $key_base = '') {
    // A couple of special cases because I'd rather arrays were indexed by
    // keys than integers. This will make the state files more stable w.r.t.
    // diffs.
    if ($key_base == 'downloads') {
        $transformed = array();
        foreach ($x as $value) {
            $transformed[pathinfo($value['url'], PATHINFO_EXTENSION)] = $value;
        }
        $x = $transformed;
    }
    else if ($key_base == 'third_party') {
        assert(count($x) == 1);
        $x = array('windows' => $x[0]);
    }

    $flat = array();
    foreach ($x as $sub_key => $value) {
        $key = $key_base ? "{$key_base}.{$sub_key}" : $sub_key;
        if (is_array($value)) {
            $flat = array_merge($flat, flatten_array($value, $key));
        }
        else {
            $flat[$key] = $value;
        }
    }
    return $flat;
}

main();
