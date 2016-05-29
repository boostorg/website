<?php

# Copyright 2007 Rene Rivera
# Copyright 2011, 2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

class BoostBookParser {
    var $document;

    function __construct($document = null) {
        if ($document) {
            $this->document = $document;
        } else {
            $this->document = new DOMDocument();
        }
    }

    function parse($filename) {
        $article = new DOMDocument();
        $article->load($filename);
        $article_node = $article->documentElement;
        if ($article_node->nodeName != 'article') {
            echo "Boostbook file is not an article: {$filename}\n";
            return;
        }

        $id = $article_node->hasAttribute('id') ?
            $article_node->getAttribute('id') : null;

        $brief_xhtml = $this->new_fragment($this->x_children(
            $article_node->getElementsByTagName('articlepurpose')->item(0)));

        $title_xhtml = $this->new_fragment($this->x_children(
            $article_node->getElementsByTagName('title')->item(0)));

        $notice_xhtml = null;
        $notice_url = null;
        $notice_node = $article_node->getElementsByTagName('notice');
        if ($notice_node->length) {
            $notice_xhtml = $this->new_fragment($this->x_children(
                $notice_node->item(0)));
            $notice_url = $notice_node->item(0)->getAttribute('url');
        }

        $documentation = null;
        $documentation_node = $article_node->getElementsByTagName('documentation');
        if ($documentation_node->length) {
            $documentation = $this->get_child($documentation_node->item(0))->data;
        }

        $pub_date = trim($article_node->getAttribute('last-revision'));

        if (!$pub_date or $pub_date[0] == '$') {
            $pub_date = 'In Progress';
            $last_modified = time();
        } else {
            $last_modified = strtotime($pub_date);
        }

        $description_xhtml = $this->x($article_node);

        return array(
            'id' => $id,
            'title_fragment' => $title_xhtml,
            'purpose_fragment' => $brief_xhtml,
            'description_fragment' => $description_xhtml,
            'notice_url' => $notice_url,
            'notice_fragment' => $notice_xhtml,
            'pub_date' => $pub_date,
            'last_modified' => $last_modified,
            'documentation' => $documentation,
        );
    }

    /** Call conversion method for node */
    function x($node) {
        $name = 'x_'.preg_replace('@[-#]@', '_', $node->nodeName);

        if (method_exists($this, $name)) {
            return $this->{$name}($node);
        } else {
            die("Unknown node type {$name}\n");
        }
    }

    function x_children($parent) {
        $result = array();
        if ($parent) foreach ($parent->childNodes as $n) {
            $result[] = $this->x($n);
        }
        return $result;
    }

    function x_article($node) {
        $description_xhtml = $this->new_fragment();
        foreach ($node->childNodes as $body_item) {
            if (in_array($body_item->nodeName, array('title', 'articleinfo'))) {
                continue;
            }
            if ($body_item->childNodes) {
                if ($this->get_child_with_tag($body_item, 'download')) {
                    continue;
                }
                if ($this->get_child_with_tag($body_item, 'download_basename')) {
                    continue;
                }
                if ($this->get_child_with_tag($body_item, 'status')) {
                    continue;
                }
                if ($this->get_child_with_tag($body_item, 'notice')) {
                    continue;
                }
                if ($this->get_child_with_tag($body_item, 'documentation')) {
                    continue;
                }
                if ($this->get_child_with_tag($body_item, 'final_documentation')) {
                    continue;
                }
            }
            $description_xhtml->appendChild($this->x($body_item));
        }

        return $description_xhtml;
    }

    function x__text($node) {
        return $this->document->createTextNode($node->data);
    }

    function x_para($node) {
        return $this->new_node('p', $this->x_children($node));
    }

    function x_simpara($node) {
        return $this->new_node('div', $this->x_children($node));
    }

    function x_ulink($node) {
        $a = $this->new_node('a', $this->x_children($node));
        $a->setAttribute('href', $node->getAttribute('url'));
        return $a;
    }

    function x_section($node) {
        $a = $this->new_node('div', $this->x_children($node));
        $a->setAttribute('id', $node->getAttribute('id'));
        return $a;
    }

    function x_title($node) {
        return $this->new_node('h3', $this->x_children($node));
    }

    function x_link($node) {
        $a = $this->new_node('span', $this->x_children($node));
        $a->setAttribute('class', 'link');
        return $a;
    }

    function x_orderedlist($node) {
        return $this->new_node('ol', $this->x_children($node));
    }

    function x_itemizedlist($node) {
        return $this->new_node('ul', $this->x_children($node));
    }

    function x_listitem($node) {
        return $this->new_node('li', $this->x_children($node));
    }

    function x_blockquote($node) {
        return $this->new_node('blockquote', $this->x_children($node));
    }

    function x_phrase($node) {
        $a = $this->new_node('span', $this->x_children($node));
        $a->setAttribute('class', $node->getAttribute('role'));
        return $a;
    }

    function x_code($node) {
        return $this->new_node('code', $this->x_children($node));
    }

    function x_macroname($node) {
        return $this->new_node('code', $this->x_children($node));
    }

    function x_classname($node) {
        return $this->new_node('code', $this->x_children($node));
    }

    function x_programlisting($node) {
        return $this->new_node('pre', $this->x_children($node));
    }

    function x_literal($node) {
        return $this->new_node('tt', $this->x_children($node));
    }

    function x_subscript($node) {
        return $this->new_node('sub', $this->x_children($node));
    }

    function x_superscript($node) {
        return $this->new_node('sup', $this->x_children($node));
    }

    function x_emphasis($node) {
        $role = '';
        if ($node->hasAttribute('role')) {
            $role = strtolower($node->getAttribute('role'));
        }

        $tags = array(
            '' => 'em',
            'bold' => 'strong',
            'strong' => 'strong'
        );

        if (!isset($tags[$role])) {
            echo "Warning: Unknown emphasis role: {$role}\n";
            $role = '';
        }

        return $this->new_node($tags[$role], $this->x_children($node));
    }

    function x_inlinemediaobject($node) {
        $image = $this->get_child_with_tag($node,'imageobject');
        if ($image) {
            $image = $this->get_child_with_tag($image,'imagedata');
            if ($image) {
                $image = $image->getAttribute('fileref');
            }
        }
        $alt = $this->get_child_with_tag($node,'textobject');
        if ($alt) {
            $alt = $this->get_child_with_tag($alt,'phrase');
            if ($alt && $alt->getAttribute('role') == 'alt') {
                $alt = trim($this->get_child($alt)->data);
            } else {
                $alt = null;
            }
        }
        if (!$alt) {
            $alt = '[]';
        }
        if ($image) {
            $img = $this->new_node('img');
            $img->setAttribute('src', $image);
            $img->setAttribute('alt', $alt);
            return $img;
        } else {
            return null;
        }
    }

    function get_child($root, $dummy = null) {
        if ($dummy) {
            die("Extra parameter in get_child.");
        }
        foreach ($root->childNodes as $n) {
            return $n;
        }
        return null;
    }

    function get_child_with_tag($root, $tag) {
        foreach ($root->childNodes as $n) {
            if ($tag == $n->nodeName) {
                return $n;
            }
        }
        return null;
    }

    function new_fragment($children = array()) {
        $result = $this->document->createDocumentFragment();
        foreach($children as $c) {
            if ($c) {
                $result->appendChild($c);
            }
        }
        return $result;
    }

    function new_node($tag, $children = array()) {
        $result = $this->document->createElement($tag);
        foreach ($children as $c) {
            if ($c) {
                $result->appendChild($c);
            }
        }
        return $result;
    }

    function new_text($tag, $data, $children = array()) {
        $result = $this->new_node(tag, $children);
        $data = trim($data);
        if (strlen($data) > 0) {
            $result->appendChild($this->document->createTextNode($data));
        }
        return $result;
    }
}
