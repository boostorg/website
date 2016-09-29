<?php
/*
  Copyright 2007 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost_config.php');

function html_encode($text) {
    return htmlentities($text, ENT_COMPAT, 'UTF-8');
}

class BoostException extends RuntimeException {}

spl_autoload_register(function($name) {
    if (!preg_match('@^[A-Za-z0-9\\\\_]*$@', $name)) {
        throw new BoostException("Invalid autoload name: {$name}");
    }

    $file_path = __DIR__.'/'
        .strtolower(preg_replace('@([a-z])([A-Z])@', '$1_$2', $name))
        .'.php';

    if (is_file($file_path)) { require_once($file_path); }
});

BoostVersion::set_current(1, 62, 0);
