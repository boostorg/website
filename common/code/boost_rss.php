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

                $item['item'] = BoostSiteTools::trim_lines($item['item']);
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

        $xml = '';
        $page_link = "http://www.boost.org/{$page->location}";

        $xml .= '<item>';

        $xml .= '<title>'.$this->encode_for_rss($page->title_xml).'</title>';
        $xml .= '<link>'.$this->encode_for_rss($page_link).'</link>';
        $xml .= '<guid>'.$this->encode_for_rss($page_link).'</guid>';

        # TODO: Convert date format?
        $xml .= '<pubDate>'.$this->encode_for_rss($page->pub_date).'</pubDate>';

        # Placing the description in a root element to make it well formed xml->
        $description = BoostSiteTools::base_links($page->description_xml, $page_link);
        $xml .= '<description>'.$this->encode_for_rss($description).'</description>';

        $xml .= '</item>';

        return(array(
            'item' => $xml,
            'quickbook' => $qbk_file,
            'last_modified' => $page->last_modified,
        ));
    }

    function encode_for_rss($x) {
        return htmlspecialchars($x, ENT_NOQUOTES);
    }
}
