<?php
# Copyright 2007 Rene Rivera
# Copyright 2011,2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)

class BoostSiteTools {
    var $root;

    function __construct($path = null) {
        if (is_null($path)) { $path = BOOST_WEBSITE_DATA_ROOT_DIR; }
        $this->root = $path;
        BoostSiteTools_Upgrades::upgrade($this);
    }

    function load_pages() {
        return new BoostPages($this->root);
    }

    function refresh_quickbook() {
        $this->update_quickbook(true);
    }

    function update_in_progress_pages() {
        $pages = $this->load_pages();
        $pages->scan_for_new_quickbook_pages();
        $pages->convert_quickbook_pages('in_progress');
        $pages->save();
    }

    function update_quickbook($refresh = false) {
        $pages = $this->load_pages();

        if (!$refresh) {
            $pages->scan_for_new_quickbook_pages();
            $pages->save();
        }

        // Translate new and changed pages

        $pages->convert_quickbook_pages($refresh ? 'refresh' : 'update');

        // Extract data for generating site from $pages:

        $history_pages = array();
        $released_versions = array();
        $beta_versions = array();
        $all_versions = array();
        $all_downloads = array();
        $news = array();

        foreach($pages->reverse_chronological_pages() as $page) {
            switch($page->section) {
            case 'news':
                if ($page->is_published()) {
                    $news[] = $page;
                }
                break;
            case 'history':
                if ($page->is_published()) {
                    $all_versions[] = $page;

                    if (!$page->is_release) {
                        $history_pages[] = $page;
                        $news[] = $page;
                    }
                    else {
                        if ($page->is_published('released')) {
                            $all_downloads[] = $page;
                            $history_pages[] = $page;
                            $released_versions[] = $page;
                            $news[] = $page;
                        }
                        else if ($page->is_published('beta')) {
                            $beta_versions[] = $page;
                        }
                    }
                }

                break;
            case 'downloads':
                if ($page->is_published('released')) {
                    $all_downloads[] = $page;
                }
                break;
            default:
                echo "Unknown website section: {$page->section}.\n";
                break;
            }
        }

        $released_versions_entries = array_map(function ($x) { return $x->index_info(); }, $released_versions);
        $beta_versions_entries = array_map(function ($x) { return $x->index_info(); }, $beta_versions);
        $history_pages_entries = array_map(function ($x) { return $x->index_info(); }, $history_pages);
        $news_entries = array_map(function ($x) { return $x->index_info(); }, $news);

        $downloads = array_filter(array(
            $this->get_downloads('live', 'Current', $released_versions_entries, 1),
            $this->get_downloads('beta', 'Beta', $beta_versions_entries),
        ));

        $index_page_variables = array(
            'history_pages' => $history_pages_entries,
            'news' => $news_entries,
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

            $rss->generate_rss_feed($pages, array(
                'path' => 'generated/downloads.rss',
                'link' => 'users/download/',
                'title' => 'Boost Downloads',
                'pages' => $all_downloads,
                'count' => 3
            ));
            $rss->generate_rss_feed($pages, array(
                'path' => 'generated/history.rss',
                'link' => 'users/history/',
                'title' => 'Boost History',
                'pages' => $history_pages,
            ));
            $rss->generate_rss_feed($pages, array(
                'path' => 'generated/news.rss',
                'link' => 'users/news/',
                'title' => 'Boost News',
                'pages' => $news,
                'count' => 5
            ));
            $rss->generate_rss_feed($pages, array(
                'path' => 'generated/dev.rss',
                'link' => '',
                'title' => 'Release notes for work in progress boost',
                'pages' => $all_versions,
                'count' => 5
            ));
        }

        # Create a list of release in reverse version order
        #
        # This is normally the default order, but it's possible that a point
        # release might be released after a later major release.

        $releases_by_version = $released_versions;
        usort($releases_by_version, function($x, $y) {
            $x_has_version = array_key_exists('version', $x->release_data);
            $y_has_version = array_key_exists('version', $y->release_data);
            if (!$x_has_version) { return $y_has_version ? 1 : 0; }
            if (!$y_has_version) { return -1; }
            return $y->release_data['version']->compare(
                $x->release_data['version']);
        });
        $latest_version = $releases_by_version[0]->release_data['version'];

        # Write out the current version for reference

        file_put_contents(__DIR__.'/../../generated/current_version.txt',
            $latest_version);

        # Update doc/.htaccess

        $final_doc_dir = $latest_version->final_doc_dir();
        $redirect_block = "# REDIRECT_UPDATE_START\n";
        $redirect_block .= "#\n";
        $redirect_block .= "# This section is automatically updated.\n";
        $redirect_block .= "# Any edits will be overwritten.\n";
        $redirect_block .= "#\n";
        $redirect_block .= "# Redirect from symbolic names to current versions.\n";
        $redirect_block .= "RewriteRule ^libs/release(/.*)?\\\$ libs/{$final_doc_dir}\\\$1 [R=303]\n";
        $redirect_block .= "RewriteRule ^libs/development(/.*)?\\\$ libs/{$final_doc_dir}\\\$1 [R=303]\n";
        $redirect_block .= "#\n";
        $redirect_block .= "# REDIRECT_UPDATE_END\n";

        $htaccss_file = __DIR__.'/../../doc/.htaccess';
        $htaccess = file_get_contents($htaccss_file);
        $count = 0;
        $htaccess = preg_replace(
            '@^# REDIRECT_UPDATE_START$.*^# REDIRECT_UPDATE_END$\n@ms',
            $redirect_block, $htaccess, -1, $count);
        if ($count != 1) {
            throw new BoostException("Error updating doc/.htaccess");
        }
        file_put_contents($htaccss_file, $htaccess);

        # Generate documentation list

        $documentation_list = <<<EOL
  <h4><a href="/doc/" class="internal">Documentation <span class=
  "link">&gt;</span></a></h4>

  <ul>
    <li><a href="/doc/libs/release/more/getting_started/">Getting Started
    <span class="link">&gt;</span></a></li>

    <li>
      <a href="/doc/libs">Libraries <span class="link">&gt;</span></a>

      <ul>
EOL;
        $first = true;
        foreach($releases_by_version as $page) {
            $documentation = $page->get_documentation();
            $version = BoostWebsite::array_get($page->release_data, 'version');
            if ($documentation && $version && $version->is_numbered_release()) {
                $documentation_list .= "\n";
                $documentation_list .= "        <li><a href=\"{$documentation}\" rel=\"nofollow\">{$version}";
                if ($first) {
                    $documentation_list .= " - Current\n";
                    $documentation_list .= "        Release <span class=\"link\">&gt;</span></a></li>\n";
                    $first = false;
                } else {
                    $documentation_list .= " <span class=\n";
                    $documentation_list .= "        \"link\">&gt;</span></a></li>\n";
                }
            }
        }

        $documentation_list .= <<<EOL
      </ul>
    </li>

    <li>
      <a href="/doc/tools.html">Tools <span class="link">&gt;</span></a>

      <ul>
        <li><a href="/tools/build/">Boost Build <span class=
        "link">&gt;</span></a></li>

        <li><a href="/tools/regression/">Regression <span class=
        "link">&gt;</span></a></li>

        <li><a href="/tools/inspect/">Inspect <span class=
        "link">&gt;</span></a></li>

        <li><a href="/doc/html/boostbook.html">BoostBook <span class=
        "link">&gt;</span></a></li>

        <li><a href="/tools/quickbook/">QuickBook <span class=
        "link">&gt;</span></a></li>

        <li><a href="/tools/bcp/">bcp <span class=
        "link">&gt;</span></a></li>

        <li><a href="/libs/wave/doc/wave_driver.html">Wave <span class=
        "link">&gt;</span></a></li>

        <li><a href="/tools/auto_index/">AutoIndex <span class=
        "link">&gt;</span></a></li>
      </ul>
    </li>
  </ul>

EOL;
        file_put_contents(__DIR__.'/../../generated/menu-doc.html', $documentation_list);

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

    static function trim_lines($x) {
        if ($x) {
            return preg_replace('@(?<! ) +$@m', '', $x);
        } else {
            return null;
        }
    }

    static function base_links($xhtml, $base_link) {
        return self::transform_links($xhtml, function($x) use ($base_link) {
            return BoostUrl::resolve($x, $base_link);
        });
    }

    static function transform_links_regex($xhtml, $pattern, $replace) {
        return self::transform_links($xhtml, function($x) use ($pattern, $replace) {
            return preg_replace($pattern, $replace, $x);
        });
    }

    static function transform_links($xhtml, $func) {
        $result = '';
        $pos = 0;

        $tag_stuff = '(?:[^<>"\']|\'[^\']*\'|"[^"]*")*';
        $value_match = '\'[^\']*\'|"[^"]*"|[^\s<>"\']*';

        preg_match_all(
            "@
            <
            (?:
                # Try to match the link values of 'img' and 'a' tags.
                (?|
                    img \b {$tag_stuff} \b src  \s* = \s* ({$value_match})
                |   a   \b {$tag_stuff} \b href \s* = \s* ({$value_match})
                )
                {$tag_stuff}
                >?
            |
                # Ignore CDATA
                !\[CDATA\[(?:.*?\]\]>|.*)
            |
                # Ignore comments
                !--.*(?:-->)?
            |
                # Ignore other <! tags.
                ![^>]*>?
            |
                # Ignore misc tags.
                [/\w]{$tag_stuff}>?
            )
            @xsmi",
            $xhtml, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        foreach ($matches as $match) {
            if (!empty($match[1][0])) {
                $string = $match[1][0];
                if ($string[0] == '"' || $string[0] == "'") {
                    $string = substr($string, 1, -1);
                }
                $string = html_entity_decode($string);
                $string = call_user_func($func, $string);
                $string = htmlspecialchars($string, ENT_COMPAT);
                $result .= substr($xhtml, $pos, $match[1][1] - $pos);
                $result .= "\"{$string}\"";
                $pos = $match[1][1] + strlen($match[1][0]);
            }
        }
        $result .= substr($xhtml, $pos);
        return $result;
    }
}

class BoostSiteTools_Upgrades {
    static $versions = array(
        1 => 'BoostSiteTools_Upgrades::old_upgrade',
        2 => 'BoostSiteTools_Upgrades::old_upgrade',
        3 => 'BoostSiteTools_Upgrades::old_upgrade',
        4 => 'BoostSiteTools_Upgrades::old_upgrade',
        5 => 'BoostSiteTools_Upgrades::update_unversioned_hash',
        6 => 'BoostSiteTools_Upgrades::clear_page_cache',
    );

    static function upgrade($site_tools) {
        $filename = $site_tools->root.'/generated/state/version.txt';
        $file_contents = trim(file_get_contents($filename));
        if (!preg_match('@^[0-9]+$@', $file_contents)) {
            throw new BoostException("Error reading state version");
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
        throw new BoostException("Old unsupported data version.");
    }

    // unversioned.qbk used to have release data, but now it doesn't, so
    // rehash it to avoid rebuilding it.
    static function update_unversioned_hash($site_tools) {
        $pages = $site_tools->load_pages();
        $unversioned = BoostWebsite::array_get($pages->pages,
            'feed/history/unversioned.qbk');
        if ($unversioned) {
            $unversioned->qbk_hash =
                $pages->calculate_qbk_hash($unversioned, 'downloads');
            $pages->save();
        }
    }

    static function clear_page_cache($site_tools) {
        $pages = $site_tools->load_pages();
        $pages->page_cache = array();
        $pages->save();
    }
}
