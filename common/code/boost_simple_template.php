<?php

require_once(__DIR__.'/boost.php');

/* Simple template library inspired by mustache.
 * Does not implement:
 *
 *    Lambdas
 *    Partials
 *    Set Delimiter
 *
 * Doesn't claim to be at all compatible with Mustache, just that it should be
 * easy to switch to a proper Mustache implementation in the future.
 */
class BoostSimpleTemplate {
    static function render($template, $params) {
        echo self::render_to_string($template, $params);
    }

    static function render_to_string($template, $params) {
        $parsed_template = self::parse_template($template);
        return self::interpret($parsed_template, $params);
    }

    static function parse_template($template) {
        preg_match_all('@
            {{(?:
                [!].*? |
                (?P<symbol_operator>[#/^&]?)\s*(?P<symbol>[\w]+)\s* |
                {\s*(?P<unescaped>[\w]+)\s*}
            )}}
            ([ #t]*\n)?
            @xsm',
            $template, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $template_parts = array();
        $operator_stack = array();
        $scope_stack = array();
        $template_stack = array();
        $last_offset = 0;

        foreach($matches as $match) {
            $operator = null;
            if (!empty($match['unescaped'][0])) {
                $operator = '&';
                $symbol = $match['unescaped'][0];
            }
            else if(!empty($match['symbol'][0])) {
                $operator = $match['symbol_operator'][0] ?: '$';
                $symbol = $match['symbol'][0];
            }

            $text_offset = $last_offset;
            $text_length = $match[0][1] - $text_offset;
            $last_offset = $match[0][1] + strlen($match[0][0]);

            if ($text_length) {
                $template_parts[] = substr($template, $text_offset, $text_length);
            }

            switch($operator) {
            case null:
                break;
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
            case '$':
            case '&':
                $template_parts[] = array(
                    'type' => $operator,
                    'symbol' => $symbol,
                );
                break;
            default:
                assert(false);
                exit(1);
            }
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
                case '&':
                    if ($value) {
                        $output .= $value;
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
