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

        SimpleTemplate::render(
            file_get_contents(__DIR__.'/../common/code/templates/libraries.htm'),
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

/* Simple template library inspired by mustache.
 * Does not implement:
 *
 *    Triple brackets for unescaped output.
 *    Lambdas
 *    Comments
 *    Parials
 *    Set Delimiter
 *
 * Doesn't claim to be at all compatible with Mustache, just that it should be
 * easy to switch to a proper Mustache implementation in the future.
 */
class SimpleTemplate {
    static function render($template, $params) {
        $parsed_template = self::parse_template($template);
        //print_r($params); exit(0);
        echo self::interpret($parsed_template, $params);
    }

    static function parse_template($template) {
        preg_match_all('@{{([#/^]?)([\w]+)}}([ #t]*\n)?@',
            $template, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $template_parts = array();
        $operator_stack = array();
        $scope_stack = array();
        $template_stack = array();
        $last_offset = 0;

        foreach($matches as $match) {
            $new_offset = $match[0][1];
            $end_offset = $match[0][1] + strlen($match[0][0]);
            $operator = $match[1][0];
            $symbol = $match[2][0];

            if ($new_offset > $last_offset) {
                $template_parts[] = substr($template, $last_offset, $new_offset - $last_offset);
            }

            switch($operator) {
            case '#':
            case '^':
                $operator_stack[] = $operator;
                $scope_stack[] = $symbol;
                $template_stack[] = $template_parts;
                $template_parts = array();
                break;
            case '/':
                if (array_pop($scope_stack) !== $symbol) {
                    // TODO: Better error message here.
                    echo "Template error.\n";
                    exit(1);
                }
                $parent_template_parts = array_pop($template_stack);
                $parent_template_parts[] = array(
                    'type' => array_pop($operator_stack),
                    'symbol' => $symbol,
                    'contents' => $template_parts,
                );
                $template_parts = $parent_template_parts;
                break;
            case '':
                $template_parts[] = array(
                    'type' => '$',
                    'symbol' => $symbol,
                );
                break;
            default:
                assert(false);
                exit(1);
            }

            $last_offset = $end_offset;
        }

        if ($scope_stack) {
            // TODO: Better error message here.
            echo "Template error.\n";
            exit(1);
        }

        $end = substr($template, $last_offset);
        if ($end) { $template_parts[] = $end; }

        return $template_parts;
    }

    static function interpret($template_array, $params) {
        $output = '';

        foreach($template_array as $template_part) {
            if (is_string($template_part)) {
                $output .= $template_part;
            } else {
                $symbol = $template_part['symbol'];
                $value = array_key_exists($symbol, $params) ?
                    $params[$symbol] : null;
                switch($template_part['type']) {
                case '$':
                    if ($value) {
                        $output .= html_encode($value);
                    }
                    break;
                case '#':
                    if ($value) {
                        $output .= self::interpret_nested_content(
                            $template_part['contents'],
                            $params,
                            $value);
                    }
                    break;
                case '^':
                    if (!$value) {
                        $output .= self::interpret(
                            $template_part['contents'],
                            $params);
                    }
                    break;
                default:
                    assert(false);
                    exit(1);
                }
            }
        }

        return $output;
    }

    static function interpret_nested_content($template_array, $params, $value) {
        if (is_object($value)) {
            return self::interpret($template_array, array_merge($params, (array) $value));
        }
        if (is_array($value)) {
            // Just checking the first key to see if this is a list or object.
            // Should probably check every key?
            reset($value);
            if (is_int(key($value))) {
                $output = '';
                foreach($value as $x) {
                    if (is_object($x)) {
                        $output .= self::interpret($template_array, array_merge($params, (array) $x));
                    }
                    else if (is_array($x)) {
                        $output .= self::interpret($template_array, array_merge($params, $x));
                    }
                    else {
                        // TODO: Better error?
                        assert(false);
                        exit(0);
                    }
                }
                return $output;
            }
            else {
                return self::interpret($template_array, array_merge($params, $value));
            }
        }
        else {
            return self::interpret($template_array, $params);
        }
    }
}

main($_GET);
