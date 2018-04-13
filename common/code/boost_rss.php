<?php
# Copyright 2011, 2015-2016 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)

class BoostRss {
    var $root;
    var $rss_state_path;
    var $rss_items;

    function __construct($root, $rss_state_path) {
        $this->root = $root;
        $this->rss_state_path = "{$root}/{$rss_state_path}";

        if (is_file($this->rss_state_path)) {
            $this->rss_items = BoostState::load($this->rss_state_path);

            // Temporary code to convert last_modified to date.
            foreach($this->rss_items as &$item) {
                if (!empty($item['last_modified']) && is_numeric($item['last_modified'])) {
                    $item['last_modified'] = new DateTime("@{$item['last_modified']}");
                }
            }
            unset($item);
        }
    }

    function generate_rss_feed($pages, $feed_data) {
        $feed_file = $feed_data['path'];
        $feed_pages = $feed_data['pages'];
        $rss_feed = $this->rss_prefix($feed_file, $feed_data);
        if (isset($feed_data['count'])) {
            $feed_pages = array_slice($feed_pages, 0, $feed_data['count']);
        }

        foreach ($feed_pages as $qbk_page) {
            $rss_item = BoostWebsite::array_get($this->rss_items, $qbk_page->qbk_file);
            if (!$rss_item || BoostWebsite::array_get($rss_item, 'update_count', 0) < $qbk_page->update_count) {
                $boostbook_values = BoostWebsite::array_get($pages->page_cache, $qbk_page->qbk_file);
                if ($boostbook_values) {
                    $rss_item = $this->generate_rss_item($qbk_page, $boostbook_values,
                        $pages->transform_page_html($qbk_page, $boostbook_values['description_xhtml']));
                    $rss_item['item'] = BoostSiteTools::trim_lines($rss_item['item']);
                    $this->rss_items[$qbk_page->qbk_file] = $rss_item;
                }
                else {
                    echo "No page contents for {$qbk_page->qbk_file}\n";
                }
            }

            if ($rss_item) {
                $rss_feed .= $rss_item['item'];
            }
        }

        $rss_feed .= $this->rss_postfix($feed_file, $feed_data);

        BoostState::save($this->rss_items, $this->rss_state_path);
        file_put_contents("{$this->root}/{$feed_file}", $rss_feed);
    }

    function rss_prefix($feed_file, $details) {
        $title = $this->encode_for_rss($details['title']);
        $link = $this->encode_for_rss("https://www.boost.org/".$details['link']);
        $self_link = $this->encode_for_rss("https://www.boost.org/".$feed_file);
        $description = '';
        $language = 'en-us';
        $copyright = 'Distributed under the Boost Software License, Version 1.0. (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)';
        return <<<EOL
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:boostbook="urn:boost-org:boostbook" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <generator>Boost Website Site Tools</generator>
    <title>{$title}</title>
    <link>{$link}</link>
    <atom:link href="{$self_link}" rel="self" type="application/rss+xml" />
    <description>{$description}</description>
    <language>{$language}</language>
    <copyright>{$copyright}</copyright>

EOL;
    }

    function rss_postfix($feed_file, $details) {
        return "\n  </channel>\n</rss>\n";
    }

    function generate_rss_item($page, $values, $description) {
        $xml = '';
        $page_link = "https://www.boost.org/{$page->location}";

        $xml .= '<item>';

        $xml .= '<title>'.$this->encode_for_rss($values['title_xml']).'</title>';
        // TODO: guid and link for beta/dev pages
        $xml .= '<link>'.$this->encode_for_rss($page_link).'</link>';
        $xml .= '<guid>'.$this->encode_for_rss($page->guid).'</guid>';

        // Q: Maybe use $page->last_modified when there's no pub_date.
        $pub_date = null;
        if ($page->release_data && array_key_exists('release_date', $page->release_data)) {
            $pub_date = $page->release_data['release_date'];
        } else {
            $pub_date = $values['pub_date'];
        }
        if ($pub_date) {
            $xml .= '<pubDate>'.$this->encode_for_rss($pub_date->format(DATE_RSS)).'</pubDate>';
        }

        # Placing the description in a root element to make it well formed xml->
        $description = BoostSiteTools::base_links($description, $page_link);
        $xml .= '<description>'.$this->encode_for_rss($description).'</description>';

        $xml .= '</item>';

        // Q: Should this be using the page last_modified, or when the RSS
        //    feed item was last modified?
        return(array(
            'item' => $xml,
            'quickbook' => $page->qbk_file,
            'last_modified' => $page->last_modified,
            'update_count' => $page->update_count,
        ));
    }

    function encode_for_rss($x) {
        return htmlspecialchars($x, ENT_NOQUOTES);
    }
}
