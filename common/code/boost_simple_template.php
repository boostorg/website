<?php

require_once(__DIR__.'/boost.php');

/* Simple mustache-like template library.
 * Does not implement:
 *
 *    Lambdas
 *    Partials
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
        $template_parts = array();
        $operator_stack = array();
        $scope_stack = array();
        $template_stack = array();
        $last_offset = 0;

        $open_delim = '{{';
        $close_delim = '}}';

        while(preg_match("@
            (?P<leading_whitespace>^[ \\t]*)?
            (?P<tag>{$open_delim}(?:
                !.*?{$close_delim} |
                (?P<symbol_operator>[#/^&]?)\\s*(?P<symbol>[\\w?!/.-]*)\\s*{$close_delim} |
                {\\s*(?P<unescaped>[\\w?!\\/.-]+)\\s*}{$close_delim} |
                =\\s*(?P<open_delim>[^=\\s]+?)\\s*(?P<close_delim>[^=\\s]+?)\\s*={$close_delim} |
                (?P<error>)
            ))
            (?P<trailing_whitespace>[ \\t]*(?:\\r?\\n|\\Z))?
            @xsm",
            $template, $match, PREG_OFFSET_CAPTURE, $last_offset)) {

            $operator = null;
            if (array_key_exists('error', $match) && $match['error'][1] != -1) {
                throw new BoostSimpleTemplateException("Invalid/unsupported tag", $match['tag'][1]);
            }
            else if (!empty($match['unescaped'][0])) {
                $operator = '&';
                $symbol = $match['unescaped'][0];
            }
            else if (!empty($match['open_delim'][0])) {
                $operator = '=';
                $symbol = null;
            }
            else if(!empty($match['symbol'][0])) {
                $operator = $match['symbol_operator'][0] ?: '$';
                $symbol = $match['symbol'][0];
            }

            if ($operator != '&' && $operator != '$' && $match['leading_whitespace'][1] != -1 && array_key_exists('trailing_whitespace', $match) && $match['trailing_whitespace'][1] != -1) {
                $text_offset = $last_offset;
                $text_length = $match[0][1] - $text_offset;
                $last_offset = $match[0][1] + strlen($match[0][0]);
            }
            else {
                $text_offset = $last_offset;
                $text_length = $match['tag'][1] - $text_offset;
                $last_offset = $match['tag'][1] + strlen($match['tag'][0]);
            }

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
                    throw new BoostSimpleTemplateException("Mismatched close tag", $match['tag'][1]);
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
            case '=':
                $open_delim = preg_quote($match['open_delim'][0], '@');
                $close_delim = preg_quote($match['close_delim'][0], '@');
                break;
            default:
                assert(false); exit(1);
            }
        }

        if ($scope_stack) {
            // Would probably be better to store the offset of the opening tag.
            throw new BoostSimpleTemplateException("Unclosed tag: ".end($scope_stack), strlen($template));
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
                $value = self::lookup($params, $template_part['symbol']);
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
                    assert(false); exit(1);
                }
            }
        }

        return $output;
    }
    
    static function lookup($params, $symbol) {
        // Q: What if that symbol starts with a '.'?
        if ($symbol == '.') {
            $symbol = array('.');
        }
        else {
            $symbol = explode('.', $symbol);
        }
        
        $value = $params;
        foreach($symbol as $symbol_part) {
            if (is_array($value) && array_key_exists($symbol_part, $value)) {
                $value = $value[$symbol_part];
            }
            // TODO: What if the object has a magic property that doesn't actually exist?
            // Or a function call or some such craziness?
            else if (is_object($value) && property_exists($value, $symbol_part)) {
                $value = $value->$symbol_part;
            }
            else {
                return null;
            }
        }
        
        return $value;
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
                        $child_params = array_merge($params, (array) $x);
                    }
                    else if (is_array($x)) {
                        $child_params = array_merge($params, $x);
                    }
                    else {
                        $child_params = $params;
                    }

                    $child_params['.'] = $x;
                    $output .= self::interpret($template_array, $child_params);
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

class BoostSimpleTemplateException extends \RuntimeException {
    var $offset;
    
    function __construct($message, $offset = null) {
        parent::__construct($message);
        $this->offset = $offset;
    }
}
