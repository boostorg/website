<?php

require_once(__DIR__.'/boost.php');

/* Simple mustache-like template library.
 * Does not implement:
 *
 *    Lambdas
 */
class BoostSimpleTemplate {
    static function render_file($path, $params) {
        $context = new BoostSimpleTemplate_Context();
        $context->path = $path;
        $context->params = $params;
        $context->partial_loader = new BoostSimpleTemplate_PartialLoader();

        $nodes = $context->partial_loader->load($path);
        if (!$nodes) {
            throw new BoostSimpleTemplate_Exception("File not found: {$path}");
        }

        return self::interpret($context, $nodes);
    }

    static function render($template, $params, $partials = null) {
        $nodes = self::parse_template($template);

        $context = new BoostSimpleTemplate_Context();
        $context->params = $params;
        $context->path = '(text_file).mustache';
        $context->partial_loader = new BoostSimpleTemplate_PartialArray();
        if ($partials) foreach($partials as $symbol => $partial) {
            $context->partial_loader->add($symbol, self::parse_template($partial));
        }

        return self::interpret($context, $nodes);
    }

    static function get_regexp($open_delim, $close_delim) {
        $open_delim = preg_quote($open_delim, '@');
        $close_delim = preg_quote($close_delim, '@');

        return "@
            (?P<leading_whitespace>^[ \\t]*)?
            (?P<tag>{$open_delim}(?:
                !(?P<comment>.*?){$close_delim} |
                (?P<symbol_operator>[#/^&>]?)\\s*(?P<symbol>[\\w?!/.-]*)\\s*{$close_delim} |
                {\\s*(?P<unescaped>[\\w?!\\/.-]+)\\s*}{$close_delim} |
                =\\s*(?P<open_delim>[^=\\s]+?)\\s*(?P<close_delim>[^=\\s]+?)\\s*={$close_delim} |
                (?P<error>)
            ))
            (?P<trailing_whitespace>[ \\t]*(?:\\r?\\n|\\Z))?
            @xsm";
    }

    static function parse_template($template) {
        $nodes = array();
        $stack = array();
        $tokenizer = self::get_regexp('{{', '}}');

        $offset = 0;
        while(preg_match($tokenizer, $template, $match, PREG_OFFSET_CAPTURE, $offset)) {
            $text_node = array(
                'offset' => $offset,
                'type' => '(text)',
                'indent_start' => $offset == 0 || $template[$offset - 1] == "\n",
            );

            $node = array(
                'offset' => $match['tag'][1],
            );

            if (self::match_exists($match, 'error')) {
                throw new BoostSimpleTemplate_Exception("Invalid/unsupported tag", $match['tag'][1]);
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
                    throw new BoostSimpleTemplate_Exception("Mismatched close tag", $node['offset']);
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
                $tokenizer = self::get_regexp($node['open'], $node['close']);
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
            throw new BoostSimpleTemplate_Exception("Unclosed tag: {$top['node']['symbol']}", $top['node']['offset']);
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
                $new_path = $node['symbol'][0] == '/' ? $node['symbol'] : dirname($context->path).'/'.$node['symbol'];
                $partial = $context->partial_loader->load($new_path);
                if ($partial) {
                    $output .= self::interpret(
                        $context->create_partial_context($new_path, $node['indentation']),
                        $partial);
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

class BoostSimpleTemplate_PartialArray {
    var $partials;

    function __construct($partials = null) {
        $this->partials = Array();
        $partials ?: Array();
    }

    function add($path, $x) {
        $this->partials[self::normalize_path($path)] = $x;
    }

    function load($path) {
        $path = $this->normalize_path($path);
        return array_key_exists($path, $this->partials) ? $this->partials[$path] : null;
    }

    function normalize_path($path) {
        $path = preg_replace('@//+@', '/', $path);

        $new_path = array();
        foreach(explode('/', $path) as $part) {
            switch($part) {
            case '':
            case '.':
                break;
            case '..':
                array_pop($new_path);
                break;
            default:
                $new_path[] = $part;
                break;
            }
        }

        return implode('/', $new_path);
    }
}

class BoostSimpleTemplate_PartialLoader {
    var $cache = array();

    function load($path) {
        $realpath = $this->normalize_path($path);
        if ($realpath) {
            if (array_key_exists($realpath, $this->cache)) {
                return $this->cache[$realpath];
            }
            else if (is_file($realpath)) {
                return $this->cache[$realpath] = BoostSimpleTemplate::parse_template(file_get_contents($realpath));
            }
            else {
                return $this->cache[$realpath] = null;
            }
        }
        else {
            return null;
        }
    }

    function normalize_path($path) {
        $realpath = realpath($path);
        if (!$realpath && !pathinfo($path, PATHINFO_EXTENSION)) { $realpath = realpath("{$path}.mustache"); }
        return $realpath;
    }
}

class BoostSimpleTemplate_Context {
    var $partial_loader = Array();
    var $path = null;
    var $params = null;
    var $parent = null;
    var $indentation = '';

    function create_child_context($params) {
        $x = clone $this;
        $x->params = is_object($params) ? (array) $params : $params;
        $x->parent = $this;
        return $x;
    }

    function create_partial_context($path, $indentation) {
        $x = clone $this;
        $x->path = $path;
        $x->indentation .= $indentation;
        return $x;
    }
}

class BoostSimpleTemplate_Exception extends BoostException {
    var $offset;
    
    function __construct($message, $offset = null) {
        parent::__construct($message);
        $this->offset = $offset;
    }
}
