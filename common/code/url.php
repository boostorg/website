<?php

/*
  Copyright 2014 Daniel James
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

// Not a full implementation.
function resolve_url($url, $base = null) {
    if (!$base) {
        $base = parse_url($_SERVER['REQUEST_URI']);
        $base['scheme'] = $_SERVER['REQUEST_SCHEME'];
        $base['host'] = $_SERVER['HTTP_HOST'];
    }
    else {
        $base = parse_url($base);
    }

    $url = parse_url($url);

    if (isset($url['scheme'])) {
        return $url;
    }

    if (isset($base['scheme'])) $url['scheme'] = $base['scheme'];

    if (!isset($url['host'])) {
        if (isset($base['host'])) $url['host'] = $base['host'];
        $url['path'] = resolve_path($url['path'], $base['path']);
    }

    return build_url($url);
}

function resolve_path($path, $base_path) {
    if($path[0] == '/') return $path;

    $base_path = explode('/', $base_path);
    array_pop($base_path); // Remove the file part of the base.
    $path = explode('/', $path);

    while (isset($path[0])) {
        if ($path[0] == '..') {
            array_pop($base_path);
            array_shift($path);
        }
        else if ($path[0] == '.') {
            array_shift($path);
        }
        else if ($path[0] != '.') {
            break;
        }
    }

    return implode('/', $base_path).'/'.implode('/', $path);
}

function build_url($url) {
    $result = '';

    if (isset($url['scheme'])) {
        $result .= "{$url['scheme']}:";
    }
    if (isset($url['host'])) {
        $result .= "//{$url['host']}";
    }
    $result .= $url['path'];
    if (isset($url['query'])) {
        $result .= "?{$url['query']}";
    }
    if (isset($url['fragment'])) {
        $result .= "?{$url['fragment']}";
    }

    return $result;
}