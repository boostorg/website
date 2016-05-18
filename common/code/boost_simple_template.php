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
        $nodes = self::parse_template($template);
        $context = new BoostSimpleTemplate_Context();
        $context->params = $params;
        return self::interpret($context, $nodes);
    }

    static function parse_template($template) {
        $nodes = array();
        $stack = array();
        $open_delim = '{{';
        $close_delim = '}}';

        $offset = 0;
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
            $template, $match, PREG_OFFSET_CAPTURE, $offset)) {

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
                $text = substr($template, $offset, $match[0][1] - $offset);
                if ($text) { $nodes[] = $text; }
                $offset = $match[0][1] + strlen($match[0][0]);
            }
            else {
                $text = substr($template, $offset, $match['tag'][1] - $offset);
                if ($text) { $nodes[] = $text; }
                $offset = $match['tag'][1] + strlen($match['tag'][0]);
            }

            switch($operator) {
            case null:
                break;
            case '#':
            case '^':
                $stack[] = array(
                    'nodes' => $nodes,
                    'offset' => $match['tag'][1],
                    'node' => array(
                        'type' => $operator,
                        'symbol' => $symbol,
                    ),
                );
                $nodes = array();
                break;
            case '/':
                $top = array_pop($stack);
                if (!$top || $top['node']['symbol'] !== $symbol) {
                    throw new BoostSimpleTemplateException("Mismatched close tag", $match['tag'][1]);
                }
                $node = $top['node'];
                $node['contents'] = $nodes;
                $nodes = $top['nodes'];
                $nodes[] = $node;
                break;
            case '$':
            case '&':
                $nodes[] = array(
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

        if ($stack) {
            $top = end($stack);
            throw new BoostSimpleTemplateException("Unclosed tag: {$top['node']['symbol']}", $top['offset']);
        }

        $end = substr($template, $offset);
        if ($end) { $nodes[] = $end; }

        return $nodes;
    }

    static function interpret($context, $nodes) {
        $output = '';

        foreach($nodes as $node) {
            if (is_string($node)) {
                $output .= $node;
            } else {
                $value = self::lookup($context, $node['symbol']);
                switch($node['type']) {
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
                            $context,
                            $node['contents'],
                            $value);
                    }
                    break;
                case '^':
                    if (!$value) {
                        $output .= self::interpret(
                            $context,
                            $node['contents']);
                    }
                    break;
                default:
                    assert(false); exit(1);
                }
            }
        }

        return $output;
    }
    
    static function lookup($context, $symbol) {
        // Deal with some special cases to make life easier.
        if (strpos($symbol, '..') !== false) {
            return null;
        }
        else if ($symbol == '.') {
            return $context->params;
        }

        // Look up the first part of the symbol from stack.
        $symbol_parts = explode('.', $symbol);
        $first_symbol = array_shift($symbol_parts);
        if (!$first_symbol) {
            $value = $context->params;
        }
        else {
            $value = null;
            for($x = $context; $x; $x = $x->parent) {
                if (is_array($x->params) && array_key_exists($first_symbol, $x->params)) {
                    $value = $x->params[$first_symbol];
                    break;
                }
            }
        }

        // Iterate over the rest of the symbol, looking up members.
        foreach($symbol_parts as $symbol_part) {
            if (is_array($value) && array_key_exists($symbol_part, $value)) {
                $value = $value[$symbol_part];
            }
            // TODO: What if the object has a magic property that doesn't actually exist?
            // Or a function call or some such craziness?
            else if (is_object($value) && property_exists($value, $symbol_part)) {
                $value = $value->$symbol_part;
            }
            else {
                $value = null;
                break;
            }
        }
        
        return $value;
    }

    static function interpret_nested_content($context, $nodes, $value) {
        if (is_array($value)) {
            // Just checking the first key to see if this is a list or object.
            // Should probably check every key?
            reset($value);
            if (is_int(key($value))) {
                $output = '';
                foreach($value as $x) {
                    $output .= self::interpret($context->create_child_context($x), $nodes);
                }
                return $output;
            }
        }

        return self::interpret($context->create_child_context($value), $nodes);
    }
}

class BoostSimpleTemplate_Context {
    var $params = null;
    var $parent = null;

    function create_child_context($params) {
        $x = clone $this;
        $x->params = is_object($params) ? (array) $params : $params;
        $x->parent = $this;
        return $x;
    }
}

class BoostSimpleTemplateException extends \RuntimeException {
    var $offset;
    
    function __construct($message, $offset = null) {
        parent::__construct($message);
        $this->offset = $offset;
    }
}
