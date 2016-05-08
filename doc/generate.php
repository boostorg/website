<?php

// Call using something like:
//
//     curl http://www.boost.org/doc/generate.php?page=libs/libraries.htm&version=1.60.0

require_once(__DIR__.'/../common/code/boost.php');

function main($args) {
    if (!array_key_exists('page', $args)) {
        echo "Missing page argument.\n";
        exit(1);
    }

    switch(strtolower(trim($args['page']))) {
    case 'libs/libraries.htm':
        $page = new LibrariesHtm($args);
        $page->display();
        break;
    default:
        echo "Unknown page: ", htmlentities($args['page']), "\n";
        exit(1);
    }
}

class LibrariesHtm {
    var $args;

    function __construct($args) {
        $this->args = $args;
    }

    function display() {
        $version = array_key_exists('version', $this->args) ? $this->args['version'] : 'master';
        include(__DIR__.'/../common/code/templates/libraries.php');
    }
}

main($_GET);
