<?php

// Call using something like:
//
//     curl http://www.boost.org/doc/generate.php?page=libs/libraries.htm&version=1.60.0

require_once(__DIR__.'/../common/code/bootstrap.php');

function main($args) {
    if (!array_key_exists('page', $args)) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Missing page argument', true, 400);
        echo "Missing page argument.\n";
        exit(1);
    }

    switch(strtolower(trim($args['page']))) {
    case 'libs/libraries.htm':
        $page = new LibrariesHtm($args);
        $page->display();
        break;
    default:
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Page not found', true, 404);
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
        $libs = BoostLibraries::load();

        $categorized = $libs->get_categorized_for_version($version, 'name');
        // TODO: Shouldn't really have to sort this here.
        uasort($categorized, function($a, $b) {
            $a = $a['title'];
            $b = $b['title'];
            if ($a === 'Miscellaneous') { $a = 'ZZZZZZZZ'; }
            if ($b === 'Miscellaneous') { $b = 'ZZZZZZZZ'; }
            return ($a > $b) ?: ($a < $b ? -1 : 0);
        });
        $alphabetic = $libs->get_for_version($version, 'name');

        $params = array(
            'categorized' => array(),
            'alphabetic' => array(),
        );

        foreach($categorized as $category) {
            $template_value = $category;
            $template_value['libraries'] = array();
            foreach($category['libraries'] as $library) {
                $template_value['libraries'][] = $this->rewrite_library($library);
            }
            $params['categorized'][] = $template_value;
        }

        foreach($alphabetic as $library) {
            $params['alphabetic'][] = $this->rewrite_library($library);
        }

        // Better support for other branches?
        $template_dir = BOOST_REPOS_DIR.'/boost-'.
            ($version == 'develop' ? 'develop' : 'master').
            '/libs/libraries.htm';
        echo BoostSimpleTemplate::render(file_get_contents($template_dir),
            $params);
    }

    function rewrite_library($lib) {
        $lib['link'] = $this->rewrite_link($lib['documentation']);
        $lib['description'] = rtrim(trim($lib['description']), '.');
        return $lib;
    }

    function rewrite_link($link) {
        if(preg_match('@^/?libs/(.*)@', $link, $matches)) {
            $link = $matches[1];
        }
        else {
            $link = '../'.ltrim($link, '/');
        }
        return preg_replace('@/$@', '/index.html', $link);
    }
}

main($_GET);
