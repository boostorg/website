<?php
# Copyright 2007 Rene Rivera
# Copyright 2011,2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

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
            $pages->scan_location_for_new_quickbook_pages('users/history/', 'feed/history/*.qbk', 'release');
            $pages->scan_location_for_new_quickbook_pages('users/news/', 'feed/news/*.qbk', 'page');
            $pages->scan_location_for_new_quickbook_pages('users/download/', 'feed/downloads/*.qbk', 'release');
            $pages->save();
        }

        // Translate new and changed pages

        $pages->convert_quickbook_pages($refresh);

        // Extract data for generating site from $pages:

        $released_versions = array();
        $beta_versions = array();
        $all_versions = array();
        $all_downloads = array();
        $news = array();

        foreach($pages->pages as $page) {
            switch($page->type) {
            case 'page':
                if ($page->is_published()) {
                    $news[] = $page;
                }
                break;
            case 'release':
                if (preg_match('@^feed/history/@', $page->qbk_file)) {
                    if ($page->is_published()) {
                        $all_versions[] = $page;
                    }

                    if ($page->is_published(array('released'))) {
                        $all_downloads[] = $page;
                        $released_versions[] = $page;
                        $news[] = $page;
                    }
                    else if ($page->is_published(array('beta'))) {
                        $beta_versions[] = $page;
                    }
                }
                else {
                    // TODO: Can probably remove this, it's only used for
                    //       one obsolete file, that doesn't seem to be
                    //       included anywhere.
                    if ($page->is_published(array('released'))) {
                        $all_downloads[] = $page;
                    }
                }
                break;
            default:
                echo "Unknown page type: {$page->type}.\n";
                break;
            }
        }

        $downloads = array_filter(array(
            $this->get_downloads('live', 'Current', $released_versions, 1),
            $this->get_downloads('beta', 'Beta', $beta_versions),
        ));

        $index_page_variables = array(
            'released_versions' => $released_versions,
            'all_versions' => $all_versions,
            'news' => $news,
            'downloads' => $downloads,
        );

        // Generate 'Index' pages

        BoostPages::write_template(
            "{$this->root}/generated/download-items.html",
            __DIR__.'/templates/download.php',
            $index_page_variables);

        BoostPages::write_template(
            "{$this->root}/generated/history-items.html",
            __DIR__.'/templates/history.php',
            $index_page_variables);

        BoostPages::write_template(
            "{$this->root}/generated/news-items.html",
            __DIR__.'/templates/news.php',
            $index_page_variables);

        BoostPages::write_template(
            "{$this->root}/generated/home-items.html",
            __DIR__.'/templates/index.php',
            $index_page_variables);

        # Generate RSS feeds

        if (!$refresh) {
            $rss = new BoostRss($this->root, "generated/state/rss-items.txt");

            $rss->generate_rss_feed(array(
                'path' => 'generated/downloads.rss',
                'link' => 'users/download/',
                'title' => 'Boost Downloads',
                'pages' => $all_downloads,
                'count' => 3
            ));
            $rss->generate_rss_feed(array(
                'path' => 'generated/history.rss',
                'link' => 'users/history/',
                'title' => 'Boost History',
                'pages' => $released_versions,
            ));
            $rss->generate_rss_feed(array(
                'path' => 'generated/news.rss',
                'link' => 'users/news/',
                'title' => 'Boost News',
                'pages' => $news,
                'count' => 5
            ));
            $rss->generate_rss_feed(array(
                'path' => 'generated/dev.rss',
                'link' => '',
                'title' => 'Release notes for work in progress boost',
                'pages' => $all_versions,
                'count' => 5
            ));
        }

        $pages->save();
    }

    function get_downloads($anchor, $label, $entries, $count = null) {
        if ($count) {
            $entries = array_slice($entries, 0, $count);
        }

        if ($entries) {
            $y = array('anchor' => $anchor, 'entries' => $entries);
            if (count($entries) == 1) {
                $y['label'] = "{$label} Release";
            } else {
                $y['label'] = "{$label} Releases";
            }
            return $y;
        }
        else {
            return null;
        }
    }

    // Some XML processing functions - TODO: find somewhere better to put these.

    static function fragment_to_string($x) {
        if ($x) {
            return preg_replace('@ +$@m', '', $x->ownerDocument->saveXML($x));
        } else {
            return null;
        }
    }

    static function base_links($node, $base_link) {
        self::transform_links($node, function($x) use ($base_link) {
            return BoostUrl::resolve($x, $base_link);
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
