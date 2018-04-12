<?php

# Copyright 2007 Rene Rivera
# Copyright 2011, 2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)

class BoostBookParser {
    function parse($filename) {
        $parser = xml_parser_create();
        $state = new BoostBookParser_State();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        if (!xml_parse_into_struct($parser, file_get_contents($filename), $state->values, $state->index)) {
            throw new BoostException("Error parsing XML");
        }
        xml_parser_free($parser);

        $article_node = $state->get_node();
        if ($article_node->get_tag() != 'article') {
            echo "Boostbook file is not an article: {$filename}\n";
            return;
        }

        $id = $article_node->get_attribute('id');

        $brief_xhtml = $this->convert_children_to_xhtml($state->get_first_in_document('articlepurpose'));
        $title_xhtml = $this->convert_children_to_xhtml($state->get_first_in_document('title'));

        $notice_xhtml = null;
        $notice_url = null;
        $notice_node = $state->get_first_in_document('notice');
        if ($notice_node) {
            $notice_xhtml = $this->convert_children_to_xhtml($notice_node);
            $notice_url = $notice_node->get_attribute('url');
        }

        $documentation = null;
        $documentation_node = $state->get_first_in_document('documentation');
        if ($documentation_node) {
            $documentation = $documentation_node->get_value();
        }

        $pub_date = $article_node->get_attribute('last-revision');

        if (!$pub_date or $pub_date[0] == '$') {
            $pub_date = null;
        } else {
            $pub_date = new DateTime($pub_date);
        }

        $description_xhtml = $this->x($state);

        return array(
            'id' => $id,
            'title_xhtml' => $title_xhtml,
            'purpose_xhtml' => $brief_xhtml,
            'description_xhtml' => $description_xhtml,
            'notice_url' => $notice_url,
            'notice_xhtml' => $notice_xhtml,
            'pub_date' => $pub_date,
            'documentation' => $documentation,
        );
    }

    function convert_children_to_xhtml($state) {
        return $state ? $this->x_children($state) : '';
    }

    /** Call conversion method for node */
    function x($state) {
        if ($state->get_type() == 'cdata') {
            return htmlspecialchars($state->get_value(), ENT_NOQUOTES);
        }
        assert($state->get_type() == 'open' || $state->get_type() == 'complete');
        $name = 'x_'.preg_replace('@[-#]@', '_', $state->get_tag());

        if (method_exists($this, $name)) {
            return $this->{$name}($state);
        } else {
            throw new BoostException("Unknown node type {$state->get_tag()}");
        }
    }

    function x_children($state) {
        $tag = $state->get_tag();
        switch($state->get_type()) {
        case 'complete':
            return htmlspecialchars($state->get_value(), ENT_NOQUOTES);
        case 'open':
            $result = htmlspecialchars($state->get_value(), ENT_NOQUOTES);
            for(++$state->pos; $state->get_type() != 'close'; ++$state->pos) {
                $result .= $this->x($state);
            }
            if ($state->get_tag() != $tag) {
                throw new BoostException("Parse error");
            }
            return $result;
        default:
            throw new BoostException("Invalid node in convert_children_to_xhtml: {$state->get_type()}");
        }
    }

    function x_article($state) {
        $description_xhtml = htmlspecialchars($state->get_value(), ENT_NOQUOTES);
        switch($state->get_type()) {
        case 'complete':
            return $description_xhtml;
        case 'open':
            break;
        case 'default':
            throw new BoostException("Error parsing article");
        }
        for(++$state->pos; $state->get_type() != 'close'; ++$state->pos) {
            if (in_array($state->get_tag(), array('title', 'articleinfo'))) {
                $this->skip_to_end_of_tag($state);
                continue;
            }
            if ($state->get_type() == 'open') {
                if ($this->skip_node_with_child($state, array(
                    'download', 'download_basename', 'status', 'notice',
                    'documentation', 'final_documentation')))
                {
                    continue;
                }
            }
            $description_xhtml .= $this->x($state);
        }

        return $description_xhtml;
    }

    function x_para($state) {
        return $this->new_node('p', $this->x_children($state));
    }

    function x_simpara($state) {
        return $this->new_node('div', $this->x_children($state));
    }

    function x_ulink($state) {
        $node = $state->get_node();
        return $this->new_node('a', $this->x_children($state),
            array('href' => $node->get_attribute('url')));
    }

    function x_section($state) {
        $node = $state->get_node();
        return $this->new_node('div', $this->x_children($state),
            array('id' => $node->get_attribute('id')));
    }

    function x_title($state) {
        return $this->new_node('h3', $this->x_children($state));
    }

    function x_link($state) {
        return $this->new_node('span', $this->x_children($state),
            array('class' => 'link'));
    }

    function x_orderedlist($state) {
        return $this->new_node('ol', $this->x_children($state));
    }

    function x_itemizedlist($state) {
        return $this->new_node('ul', $this->x_children($state));
    }

    function x_listitem($state) {
        return $this->new_node('li', $this->x_children($state));
    }

    function x_blockquote($state) {
        return $this->new_node('blockquote', $this->x_children($state));
    }

    function x_phrase($state) {
        $node = $state->get_node();
        return $this->new_node('span', $this->x_children($state),
            array('class' => $node->get_attribute('role')));
    }

    function x_code($state) {
        return $this->new_node('code', $this->x_children($state));
    }

    function x_macroname($state) {
        return $this->new_node('code', $this->x_children($state));
    }

    function x_classname($state) {
        return $this->new_node('code', $this->x_children($state));
    }

    function x_programlisting($state) {
        return $this->new_node('pre', $this->x_children($state));
    }

    function x_literal($state) {
        return $this->new_node('tt', $this->x_children($state));
    }

    function x_subscript($state) {
        return $this->new_node('sub', $this->x_children($state));
    }

    function x_superscript($state) {
        return $this->new_node('sup', $this->x_children($state));
    }

    function x_emphasis($state) {
        $node = $state->get_node();
        $role = '';
        $role = strtolower($node->get_attribute('role'));

        $tags = array(
            '' => 'em',
            'bold' => 'strong',
            'strong' => 'strong'
        );

        if (!isset($tags[$role])) {
            echo "Warning: Unknown emphasis role: {$role}\n";
            $role = '';
        }

        return $this->new_node($tags[$role], $this->x_children($state));
    }

    function x_inlinemediaobject($state) {
        $image = $this->get_child_with_tag($state,'imageobject');
        if ($image) {
            $image = $this->get_child_with_tag($image,'imagedata');
            if ($image) {
                $image = $image->get_attribute('fileref');
            }
        }
        $alt = $this->get_child_with_tag($state,'textobject');
        if ($alt) {
            $alt = $this->get_child_with_tag($alt,'phrase');
            if ($alt && $alt->get_attribute('role') == 'alt') {
                $alt = $this->convert_children_to_xhtml($alt);
            } else {
                $alt = null;
            }
        }
        if (!$alt) {
            $alt = '[]';
        }
        $this->skip_to_end_of_tag($state);
        if ($image) {
            return $this->new_node('img', '', array(
                'src' => $image,
                'alt' => $alt));
        } else {
            return '';
        }
    }

    function get_child_with_tag($state, $tag) {
        switch ($state->get_type()) {
        case 'complete':
            return;
        case 'open':
            $depth = 0;
            $state = clone $state;

            while (true) {
                ++$state->pos;
                if ($depth == 0 && $state->get_tag('tag') == $tag &&
                    in_array($state->get_type(), array('open', 'complete')))
                {
                    return $state;
                }

                if ($state->get_type() == 'close') {
                    if ($depth == 0) { return null; }
                    else { --$depth; }
                }
                else if ($state->get_type() == 'open') {
                    ++$depth;
                }
            }
            break;
        default:
            throw new BoostException("Invalid node in skip_to_end_of_tag");
        }
    }

    function skip_to_end_of_tag($state) {
        switch ($state->get_type()) {
        case 'complete':
            return;
        case 'open':
            $depth = 0;
            for(++$state->pos; $depth || $state->get_type() != 'close'; ++$state->pos) {
                switch($state->get_type()) {
                case 'open': ++$depth; break;
                case 'close': --$depth; break;
                }
            }
            return;
        default:
            throw new BoostException("Invalid node in skip_to_end_of_tag");
        }
    }

    function skip_node_with_child($state, $child_tags) {
        switch ($state->get_type()) {
        case 'complete':
            return false;
        case 'open':
            $found = false;
            $new_state = clone $state;
            $depth = 0;
            for(++$new_state->pos; $depth || $new_state->get_type() != 'close'; ++$new_state->pos) {
                switch($new_state->get_type()) {
                case 'open': ++$depth; break;
                case 'close': --$depth; break;
                }

                if ($depth == 0 && in_array($new_state->get_tag(), $child_tags) &&
                    in_array($new_state->get_type(), array('open', 'complete')))
                {
                    $found = true;
                }
            }
            if ($found) {
                $state->pos = $new_state->pos;
            }
            return $found;
        default:
            throw new BoostException("Invalid node in skip_to_end_of_tag");
        }
    }

    function new_node($tag, $content = '', $attributes = array()) {
        $result = "<{$tag}";
        foreach($attributes as $key => $value) {
            $result .= " {$key}=\"".htmlspecialchars($value, ENT_COMPAT)."\"";
        }
        if (is_null($content) || $content == '') {
            $result .= "/>";
        }
        else {
            $result .= ">";
            $result .= $content;
            $result .= "</{$tag}>";
        }
        return $result;
    }
}

class BoostBookParser_State {
    var $values;
    var $index;
    var $pos = 0;

    function get_node() {
        // Pretty cheesy, but shouldn't be too inefficient as it's a
        // shallow clone.
        return clone $this;
    }

    function get_type() {
        return $this->values[$this->pos]['type'];
    }

    function get_tag() {
        return $this->values[$this->pos]['tag'];
    }

    function get_value() {
        return array_key_exists('value', $this->values[$this->pos]) ?
            $this->values[$this->pos]['value'] : '';
    }

    function get_attribute($name) {
        if (array_key_exists('attributes', $this->values[$this->pos]) &&
            array_key_exists($name, $this->values[$this->pos]['attributes']))
        {
            return $this->values[$this->pos]['attributes'][$name];
        }
        else {
            return null;
        }
    }

    function get_first_in_document($tag) {
        if (!array_key_exists($tag, $this->index)) { return null; }
        $x = clone $this;
        $x->pos = $this->index[$tag][0];
        return $x;
    }
}
