<?php
# Copyright 2011, 2015-2016 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

class BoostRss {
    var $root;
    var $rss_state_path;
    var $rss_items;

    function __construct($root, $rss_state_path) {
        $this->root = $root;
        $this->rss_state_path = "{$root}/{$rss_state_path}";

        if (is_file($this->rss_state_path)) {
            $this->rss_items = BoostState::load($this->rss_state_path);
        }
    }

    function generate_rss_feed($feed_data) {
        $feed_file = $feed_data['path'];
        $feed_pages = $feed_data['pages'];
        $rss_feed = $this->rss_prefix($feed_file, $feed_data);
        if (isset($feed_data['count'])) {
            $feed_pages = array_slice($feed_pages, 0, $feed_data['count']);
        }

        foreach ($feed_pages as $qbk_page) {
            $item_xml = null;

            if ($qbk_page->loaded) {
                $item = $this->generate_rss_item($qbk_page->qbk_file, $qbk_page);

                $item['item'] = BoostSiteTools::fragment_to_string($item['item']);
                $this->rss_items[$qbk_page->qbk_file] = $item;
                BoostState::save($this->rss_items, $this->rss_state_path);

                $rss_feed .= $item['item'];
            } else if (isset($this->rss_items[$qbk_page->qbk_file])) {
                $rss_feed .= $this->rss_items[$qbk_page->qbk_file]['item'];
            } else {
                echo "Missing entry for {$qbk_page->qbk_file}\n";
            }
        }

        $rss_feed .= $this->rss_postfix($feed_file, $feed_data);

        $output_file = fopen("{$this->root}/{$feed_file}", 'wb');
        fwrite($output_file, $rss_feed);
        fclose($output_file);
    }

    function rss_prefix($feed_file, $details) {
        $title = $this->encode_for_rss($details['title']);
        $link = $this->encode_for_rss("http://www.boost.org/".$details['link']);
        $description = '';
        $language = 'en-us';
        $copyright = 'Distributed under the Boost Software License, Version 1.0. (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)';
        return <<<EOL
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:boostbook="urn:boost.org:boostbook">
  <channel>
    <generator>Boost Website Site Tools</generator>
    <title>{$title}</title>
    <link>{$link}</link>
    <description>{$description}</description>
    <language>{$language}</language>
    <copyright>{$copyright}</copyright>

EOL;
    }

    function rss_postfix($feed_file, $details) {
        return "\n  </channel>\n</rss>\n";
    }

    function generate_rss_item($qbk_file, $page) {
        assert($page->loaded);

        $rss_xml = new DOMDocument();
        $rss_xml->loadXML(<<<EOL
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:boostbook="urn:boost.org:boostbook">
</rss>
EOL
        );

        $page_link = "http://www.boost.org/{$page->location}";

        $item = $rss_xml->createElement('item');

        $node = new DOMDocument();
        $node->loadXML('<title>'.$this->encode_for_rss($page->title_xml).'</title>');
        $item->appendChild($rss_xml->importNode($node->documentElement, true));

        $node = new DOMDocument();
        $node->loadXML('<link>'.$this->encode_for_rss($page_link).'</link>');
        $item->appendChild($rss_xml->importNode($node->documentElement, true));

        $node = new DOMDocument();
        $node->loadXML('<guid>'.$this->encode_for_rss($page_link).'</guid>');
        $item->appendChild($rss_xml->importNode($node->documentElement, true));

        # TODO: Convert date format?
        $node = $rss_xml->createElement('pubDate');
        $node->appendChild($rss_xml->createTextNode($page->pub_date));
        $item->appendChild($node);

        $node = $rss_xml->createElement('description');
        # Placing the description in a root element to make it well formed xml->
        $description = new DOMDocument();
        $description->loadXML('<x>'.$this->encode_for_rss($page->description_xml).'</x>');

        BoostSiteTools::base_links($description, $page_link);
        foreach($description->firstChild->childNodes as $child) {
            $node->appendChild($rss_xml->createTextNode(
                $description->saveXML($child)));
        }
        $item->appendChild($node);

        return(array(
            'item' => $item,
            'quickbook' => $qbk_file,
            'last_modified' => $page->last_modified,
        ));
    }

    function encode_for_rss($x) {
        return $x;
    }
}
