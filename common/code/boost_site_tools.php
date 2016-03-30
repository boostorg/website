<?php
# Copyright 2007 Rene Rivera
# Copyright 2011,2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

require_once(__DIR__.'/url.php');

class BoostSiteTools {
    var $root;

    function __construct($path) {
        $this->root = $path;
        BoostSiteTools_Upgrades::upgrade($this);
    }

    function load_pages() {
        return new BoostPages($this->root, "generated/state/feed-pages.txt");
    }

    function refresh_quickbook() {
        $this->update_quickbook(true);
    }

    function update_quickbook($refresh = false) {
        $pages = $this->load_pages();
        if (!$refresh) {
            $this->scan_for_new_quickbook_pages($pages);
        }

        // Translate new and changed pages

        $pages->convert_quickbook_pages($refresh);

        // Generate 'Index' pages

        $downloads = array();
        foreach (BoostPageSettings::$downloads as $x) {
            $entries = $pages->match_pages($x['matches'], null, true);
            if (isset($x['count'])) {
                $entries = array_slice($entries, 0, $x['count']);
            }
            if ($entries) {
                $y = array('anchor' => $x['anchor'], 'entries' => $entries);
                if (count($entries) == 1) {
                    $y['label'] = $x['single'];
                } else {
                    $y['label'] = $x['plural'];
                }
                $downloads[] = $y;
            }
        }

        $index_page_variables = compact('pages', 'downloads');

        foreach (BoostPageSettings::$index_pages as $index_page => $template) {
            BoostPages::write_template(
                "{$this->root}/{$index_page}",
                __DIR__.'/'.$template,
                $index_page_variables);
        }

        # Generate RSS feeds

        if (!$refresh) {
            $rss_items = BoostState::load(
                "{$this->root}/generated/state/rss-items.txt");

            foreach (BoostPageSettings::$feeds as $feed_file => $feed_data) {
                $rss_feed = $this->rss_prefix($feed_file, $feed_data);

                $feed_pages = $pages->match_pages($feed_data['matches']);
                if (isset($feed_data['count'])) {
                    $feed_pages = array_slice($feed_pages, 0, $feed_data['count']);
                }

                foreach ($feed_pages as $qbk_page) {
                    $item_xml = null;

                    if ($qbk_page->loaded) {
                        $item = $this->generate_rss_item($qbk_page->qbk_file, $qbk_page);

                        $item['item'] = self::fragment_to_string($item['item']);
                        $rss_items[$qbk_page->qbk_file] = $item;
                        BoostState::save($rss_items, "{$this->root}/generated/state/rss-items.txt");

                        $rss_feed .= $item['item'];
                    } else if (isset($rss_items[$qbk_page->qbk_file])) {
                        $rss_feed .= $rss_items[$qbk_page->qbk_file]['item'];
                    } else {
                        echo "Missing entry for {$qbk_page->qbk_file}\n";
                    }
                }

                $rss_feed .= $this->rss_postfix($feed_file, $feed_data);

                $output_file = fopen("{$this->root}/{$feed_file}", 'wb');
                fwrite($output_file, $rss_feed);
                fclose($output_file);
            }
        }

        $pages->save();
    }

    function scan_for_new_quickbook_pages($pages) {
        foreach (BoostPageSettings::$pages as $location => $pages_data) {
            foreach ($pages_data['src_files'] as $src_file_pattern) {
                foreach (glob("{$this->root}/{$src_file_pattern}") as $qbk_file) {
                    assert(strpos($qbk_file, $this->root) === 0);
                    $qbk_file = substr($qbk_file, strlen($this->root) + 1);
                    echo $qbk_file, "\n";
                    $pages->add_qbk_file($qbk_file, $location, $pages_data);
                }
            }
        }

        $pages->save();
    }

################################################################################

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

    static function fragment_to_string($x) {
        if ($x) {
            return preg_replace('@ +$@m', '', $x->ownerDocument->saveXML($x));
        } else {
            return null;
        }
    }

    static function base_links($node, $base_link) {
        self::transform_links($node, function($x) use ($base_link) {
            return resolve_url($x, $base_link);
        });
    }

    static function transform_links($node, $func) {
        self::transform_links_impl($node, 'a', 'href', $func);
        self::transform_links_impl($node, 'img', 'src', $func);
    }

    static function transform_links_impl($node, $tag_name, $attribute, $func) {
        if ($node->nodeType == XML_ELEMENT_NODE ||
            $node->nodeType == XML_DOCUMENT_NODE)
        {
            foreach ($node->getElementsByTagName($tag_name) as $x) {
                $x->setAttribute($attribute,
                    call_user_func($func, $x->getAttribute($attribute)));
            }
        }
        else if ($node->nodeType == XML_DOCUMENT_FRAG_NODE) {
            foreach ($node->childNodes as $x) {
                self::transform_links_impl($x, $tag_name, $attribute, $func);
            }
        }
    }
}

class BoostSiteTools_Upgrades {
    static $versions = [
        1 => 'BoostSiteTools_Upgrades::old_upgrade',
        2 => 'BoostSiteTools_Upgrades::old_upgrade',
        3 => 'BoostSiteTools_Upgrades::old_upgrade',
        4 => 'BoostSiteTools_Upgrades::old_upgrade',
    ];

    static function upgrade($site_tools) {
        $filename = $site_tools->root.'/generated/state/version.txt';
        $file_contents = trim(file_get_contents($filename));
        if (!preg_match('@^[0-9]+$@', $file_contents)) {
            throw new RuntimeException("Error reading state version");
        }
        $current_version = intval($file_contents);
        foreach (self::$versions as $version => $upgrade_function) {
            if ($current_version < $version) {
                call_user_func($upgrade_function, $site_tools);
                $current_version = $version;
                file_put_contents($filename, $current_version);
            }
        }
    }

    static function old_upgrade() {
        throw new RuntimeException("Old unsupported data version.");
    }
}
