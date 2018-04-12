<?php

/*
  Copyright 2014 Daniel James
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/

class BoostUrl {
// Not a full implementation.
static function resolve($url, $base = null) {
    if (!$base) {
        $base_parts = parse_url($_SERVER['REQUEST_URI']);
        $base_parts['host'] = $_SERVER['HTTP_HOST'];
        $base_parts['scheme'] = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
    }
    else {
        $base_parts = parse_url($base);
    }

    $url_parts = parse_url($url);

    if (isset($url_parts['scheme'])) {
        return $url;
    }

    if (isset($base_parts['scheme'])) $url_parts['scheme'] = $base_parts['scheme'];

    if (!isset($url_parts['host'])) {
        if (isset($base_parts['host'])) $url_parts['host'] = $base_parts['host'];
        $url_parts['path'] = self::resolve_path($url_parts['path'], $base_parts['path']);
    }

    return self::build_url($url_parts);
}

private static function resolve_path($path, $base_path) {
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

private static function build_url($url) {
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
        $result .= "#{$url['fragment']}";
    }

    return $result;
}
}
