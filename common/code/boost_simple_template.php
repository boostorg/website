<?php

require_once(__DIR__.'/boost.php');

/* Simple mustache-like template library.
 * Does not implement:
 *
 *    Lambdas
 */
class BoostSimpleTemplate {
    static function render($template, $params, $partials = null) {
        echo self::render_to_string($template, $params, $partials);
    }

    static function render_to_string($template, $params, $partials = null) {
        $nodes = self::parse_template($template);

        $context = new BoostSimpleTemplate_Context();
        $context->params = $params;
        if ($partials) foreach($partials as $symbol => $partial) {
            $context->partials[$symbol] = self::parse_template($partial);
        }

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
                !(?P<comment>.*?){$close_delim} |
                (?P<symbol_operator>[#/^&>]?)\\s*(?P<symbol>[\\w?!/.-]*)\\s*{$close_delim} |
                {\\s*(?P<unescaped>[\\w?!\\/.-]+)\\s*}{$close_delim} |
                =\\s*(?P<open_delim>[^=\\s]+?)\\s*(?P<close_delim>[^=\\s]+?)\\s*={$close_delim} |
                (?P<error>)
            ))
            (?P<trailing_whitespace>[ \\t]*(?:\\r?\\n|\\Z))?
            @xsm",
            $template, $match, PREG_OFFSET_CAPTURE, $offset)) {

            $text_node = array(
                'offset' => $offset,
                'type' => '(text)',
                'indent_start' => $offset == 0 || $template[$offset - 1] == "\n",
            );

            $node = array(
                'offset' => $match['tag'][1],
            );

            if (self::match_exists($match, 'error')) {
                throw new BoostSimpleTemplateException("Invalid/unsupported tag", $match['tag'][1]);
            }
            else if (self::match_exists($match, 'unescaped')) {
                $node['type'] = '&';
                $node['symbol'] = $match['unescaped'][0];
            }
            else if (self::match_exists($match, 'open_delim')) {
                $node['type'] = '=';
                $node['open'] = $match['open_delim'][0];
                $node['close'] = $match['close_delim'][0];
            }
            else if (self::match_exists($match, 'symbol')) {
                $node['type'] = $match['symbol_operator'][0] ?: '(variable)';
                $node['symbol'] = $match['symbol'][0];
            }
            else if (self::match_exists($match, 'comment')) {
                $node['type'] = '!';
                $node['content'] = $match['comment'][0];
            }
            else {
                assert(false);
            }

            $standalone = $node['type'] != '&' && $node['type'] != '(variable)' &&
                self::match_exists($match, 'leading_whitespace') &&
                self::match_exists($match, 'trailing_whitespace');

            if ($standalone) {
                $text_node['content'] = substr($template, $offset, $match[0][1] - $offset);
                $node['indent_start'] = false;
                $node['indentation'] = $match['leading_whitespace'][0];
                $offset = $match[0][1] + strlen($match[0][0]);
            }
            else {
                $text_node['content'] = substr($template, $offset, $match['tag'][1] - $offset);
                $node['indent_start'] = $match['tag'][1] == 0 || $template[$match['tag'][1] - 1] == "\n";
                $node['indentation'] = '';
                $offset = $match['tag'][1] + strlen($match['tag'][0]);
            }

            if ($text_node['content']) {
                $nodes[] = $text_node;
            }

            switch($node['type']) {
            case '!':
                break;
            case '#':
            case '^':
                $stack[] = array(
                    'nodes' => $nodes,
                    'node' => $node,
                );
                $nodes = array();
                break;
            case '/':
                $top = array_pop($stack);
                if (!$top || $top['node']['symbol'] !== $node['symbol']) {
                    throw new BoostSimpleTemplateException("Mismatched close tag", $node['offset']);
                }
                $node = $top['node'];
                $node['contents'] = $nodes;
                $nodes = $top['nodes'];
                $nodes[] = $node;
                break;
            case '(variable)':
            case '&':
                $nodes[] = $node;
                break;
            case '=':
                $open_delim = preg_quote($node['open'], '@');
                $close_delim = preg_quote($node['close'], '@');
                break;
            case '>':
                $nodes[] = $node;
                break;
            default:
                assert(false); exit(1);
            }
        }

        if ($stack) {
            $top = end($stack);
            throw new BoostSimpleTemplateException("Unclosed tag: {$top['node']['symbol']}", $top['node']['offset']);
        }

        $end = substr($template, $offset);
        if ($end || !$nodes) {
            $nodes[] = array(
                'offset' => $offset,
                'type' => '(text)',
                'indent_start' => $offset == 0 || $template[$offset - 1] == "\n",
                'content' => $end,
            );
        }

        return $nodes;
    }

    static function interpret($context, $nodes) {
        $output = '';

        foreach($nodes as $node) {
            if (!empty($node['indent_start'])) {
                $output .= $context->indentation;
            }
                switch($node['type']) {
                case '(text)':
                    $output .= preg_replace('@^(?!\A)@m', $context->indentation, $node['content']);
                    break;
                case '(variable)':
                    $value = self::lookup($context, $node['symbol']);
                    if ($value) {
                        $output .= html_encode($value);
                    }
                    break;
                case '&':
                    $value = self::lookup($context, $node['symbol']);
                    if ($value) {
                        $output .= $value;
                    }
                    break;
                case '#':
                    $value = self::lookup($context, $node['symbol']);
                    if ($value) {
                        $output .= self::interpret_nested_content(
                            $context,
                            $node['contents'],
                            $value);
                    }
                    break;
                case '^':
                    $value = self::lookup($context, $node['symbol']);
                    if (!$value) {
                        $output .= self::interpret(
                            $context,
                            $node['contents']);
                    }
                    break;
                case '>':
                    if (array_key_exists($node['symbol'], $context->partials)) {
                        $output .= self::interpret(
                            $context->create_partial_context($node['indentation']),
                            $context->partials[$node['symbol']]);
                    }
                    break;
                default:
                    assert(false); exit(1);
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

    static function match_exists($match, $key) {
        return array_key_exists($key, $match) && $match[$key][1] != -1;
    }
}

class BoostSimpleTemplate_Context {
    var $partials = Array();
    var $params = null;
    var $parent = null;
    var $indentation = '';

    function create_child_context($params) {
        $x = clone $this;
        $x->params = is_object($params) ? (array) $params : $params;
        $x->parent = $this;
        return $x;
    }

    function create_partial_context($indentation) {
        $x = clone $this;
        $x->indentation .= $indentation;
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
