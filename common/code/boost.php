<?php
/*
  Copyright 2007 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost_config.php');

class BoostWebsite {
    static function array_get($array, $key, $default = null) {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }
}

if (defined('ENT_SUBSTITUTE')) {
    function html_encode($text) {
        return htmlentities($text, ENT_SUBSTITUTE, 'UTF-8');
    }
} else {
    function html_encode($text) {
        if (!preg_match('//u', $text)) {
            $text = preg_replace('/[\x80-\xFF]/', "\xef\xbf\xbd", $text);
        }
        return htmlentities($text, ENT_COMPAT, 'UTF-8');
    }
}

class BoostException extends RuntimeException {}

spl_autoload_register(function($name) {
    if (!preg_match('@^[A-Za-z0-9\\\\_]*$@', $name)) {
        throw new BoostException("Invalid autoload name: {$name}");
    }

    $path = preg_replace('@([a-z])([A-Z])@', '$1_$2', $name);
    $path = str_replace('\\', '/', $path);
    $path = strtolower($path);
    $file_path = __DIR__.'/'.$path.'.php';

    if (is_file($file_path)) { require_once($file_path); }
});
