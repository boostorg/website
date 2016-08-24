<?php
# Copyright 2011, 2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

class BoostPages {
    var $root;
    var $hash_file;
    var $release_file;
    var $pages = Array();
    var $release_data = Array();

    function __construct($root, $hash_file, $release_file) {
        $this->root = $root;
        $this->hash_file = "{$root}/{$hash_file}";
        $this->release_file = "{$root}/{$release_file}";

        if (is_file($this->release_file)) {
            $this->release_data = json_decode(
                file_get_contents($this->release_file), true);
            if (is_null($this->release_data)) {
                // Q: Exception? Fallback?
                echo "Error decoding release data.\n";
                exit(0);
            }
        }

        if (is_file($this->hash_file)) {
            foreach(BoostState::load($this->hash_file) as $qbk_file => $record) {
                $this->pages[$qbk_file]
                    = new BoostPages_Page($qbk_file, $this->get_release_data($qbk_file), $record);
            }

            // Sort pages in reverse chronological order.
            $pub_date_order = array();
            $last_published_order = array();
            $unpublished_date = new DateTime("+10 years");
            foreach($this->pages as $index => $page) {
                $pub_date_order[$index] = $page->pub_date ?: $unpublished_date;
                $last_published_order[$index] = $page->last_modified;
            }
            array_multisort(
                $pub_date_order, SORT_DESC,
                $last_published_order, SORT_DESC,
                $this->pages);
        }
    }

    function save() {
        BoostState::save(
            array_map(function($page) { return $page->state(); }, $this->pages),
            $this->hash_file);
    }

    function scan_location_for_new_quickbook_pages($dir_location, $src_file_glob, $type) {
        foreach (glob("{$this->root}/{$src_file_glob}") as $qbk_file) {
            assert(strpos($qbk_file, $this->root) === 0);
            $qbk_file = substr($qbk_file, strlen($this->root) + 1);
            $this->add_qbk_file($qbk_file, $dir_location, $type);
        }
    }

    function get_release_data($qbk_file) {
        foreach($this->release_data as $release) {
            if ($release['release_notes'] === $qbk_file) {
                return $release;
            }
        }
        return null;
    }

    function add_qbk_file($qbk_file, $dir_location, $type) {
        $release_data = $this->get_release_data($qbk_file);

        $context = hash_init('sha256');
        hash_update($context, json_encode($release_data));
        hash_update($context, str_replace("\r\n", "\n",
            file_get_contents("{$this->root}/{$qbk_file}")));
        $qbk_hash = hash_final($context);

        $record = null;

        if (!isset($this->pages[$qbk_file])) {
            $this->pages[$qbk_file] = $record = new BoostPages_Page($qbk_file, $release_data);
        } else {
            $record = $this->pages[$qbk_file];
            if ($record->dir_location) {
                assert($record->dir_location == $dir_location);
            }
            if ($record->qbk_hash == $qbk_hash) {
                return;
            }
            if ($record->page_state != 'new') {
                $record->page_state = 'changed';
            }
        }

        $record->qbk_hash = $qbk_hash;
        $record->dir_location = $dir_location;
        $record->type = $type;
        $record->last_modified = new DateTime();

        if (!in_array($record->type, array('release', 'page'))) {
            throw new RuntimeException("Unknown record type: ".$record->type);
        }
    }

    function convert_quickbook_pages($refresh = false) {
        try {
            BoostSuperProject::run_process('quickbook --version');
        }
        catch(ProcessError $e) {
            echo "Problem running quickbook, will not convert quickbook articles.\n";
            return;
        }

        $bb_parser = new BoostBookParser();

        foreach ($this->pages as $page => $page_data) {
            if ($page_data->page_state || $refresh) {
                $xml_filename = tempnam(sys_get_temp_dir(), 'boost-qbk-');
                try {
                    echo "Converting ", $page, ":\n";
                    BoostSuperProject::run_process("quickbook --output-file {$xml_filename} -I {$this->root}/feed {$this->root}/{$page}");
                    $page_data->load_boostbook_data($bb_parser->parse($xml_filename), $refresh);
                } catch (Exception $e) {
                    unlink($xml_filename);
                    throw $e;
                }
                unlink($xml_filename);

                $template_vars = array(
                    'history_style' => '',
                    'full_title_xml' => $page_data->full_title_xml(),
                    'title_xml' => $page_data->title_xml,
                    'note_xml' => '',
                    'web_date' => $page_data->web_date(),
                    'documentation_para' => '',
                    'download_table' => $page_data->download_table(),
                    'description_xml' => $page_data->description_xml,
                );
                if ($page_data->type == 'release' && ($page_data->get_release_status() ?: 'dev') === 'dev') {
                    $template_vars['note_xml'] = <<<EOL
                        <div class="section-alert"><p>Note: This release is
                        still under development. Please don't use this page as
                        a source of information, it's here for development
                        purposes only. Everything is subject to
                        change.</p></div>
EOL;
                }

                if ($page_data->array_get($page_data->release_data, 'documentation')) {
                    $template_vars['documentation_para'] = '              <p><a href="'.html_encode($page_data->array_get($page_data->release_data, 'documentation')).'">Documentation</a>';
                }

                if (strpos($page_data->location, 'users/history/') === 0) {
                    $template_vars['history_style'] = <<<EOL

  <style type="text/css">
/*<![CDATA[*/
  #content .news-description ul {
    list-style: none;
  }
  #content .news-description ul ul {
    list-style: circle;
  }
  /*]]>*/
  </style>

EOL;
                }

                self::write_template(
                    "{$this->root}/{$page_data->location}",
                    __DIR__."/templates/entry.php",
                    $template_vars);
            }
        }
    }

    static function write_template($_location, $_template, $_vars) {
        ob_start();
        extract($_vars);
        include($_template);
        $r = ob_get_contents();
        ob_end_clean();
        file_put_contents($_location, $r);
    }
}

class BoostPages_Page {
    var $qbk_file;

    var $release_data;
    var $type, $page_state, $dir_location, $location;
    var $id, $title_xml, $purpose_xml, $notice_xml, $notice_url;
    var $last_modified, $pub_date;
    var $documentation, $qbk_hash;

    function __construct($qbk_file, $release_data = null, $attrs = array('page_state' => 'new')) {
        $this->qbk_file = $qbk_file;

        $this->type = $this->array_get($attrs, 'type');
        $this->page_state = $this->array_get($attrs, 'page_state');
        $this->dir_location = $this->array_get($attrs, 'dir_location');
        $this->location = $this->array_get($attrs, 'location');
        $this->id = $this->array_get($attrs, 'id');
        $this->title_xml = $this->array_get($attrs, 'title');
        $this->purpose_xml = $this->array_get($attrs, 'purpose');
        $this->notice_xml = $this->array_get($attrs, 'notice');
        $this->notice_url = $this->array_get($attrs, 'notice_url');
        $this->last_modified = $this->array_get($attrs, 'last_modified');
        $this->pub_date = $this->array_get($attrs, 'pub_date');
        $this->qbk_hash = $this->array_get($attrs, 'qbk_hash');

        $this->loaded = false;

        if (is_string($this->pub_date)) {
            $this->pub_date = $this->pub_date == 'In Progress' ?
                null : new DateTime($this->pub_date);
        }
        else if (is_numeric($this->pub_date)) {
            $this->pub_date = new DateTime("@{$this->pub_date}");
        }

        if (is_string($this->last_modified)) {
            $this->last_modified = new DateTime($this->last_modified);
        }
        else if (is_numeric($this->last_modified)) {
            $this->last_modified = new DateTime("@{$this->last_modified}");
        }

        $this->set_release_data($release_data);
    }

    function set_release_data($release_data) {
        if ($release_data) {
            assert($this->type === 'release');

            if (!array_key_exists('release_status', $release_data)) {
                $release_data['release_status'] = $this->pub_date ? 'released' : 'dev';
            }
            if (!in_array($release_data['release_status'], array('released', 'beta', 'dev'))) {
                echo "Error: Unknown release status: {$this->array_get($release_data, 'release_status')}.\n";
                exit(0);
            }
        }

        $this->release_data = $release_data;
    }

    function state() {
        return array(
            'type' => $this->type,
            'page_state' => $this->page_state,
            'dir_location' => $this->dir_location,
            'location' => $this->location,
            'id'  => $this->id,
            'title' => $this->title_xml,
            'purpose' => $this->purpose_xml,
            'notice' => $this->notice_xml,
            'notice_url' => $this->notice_url,
            'last_modified' => $this->last_modified,
            'pub_date' => $this->pub_date,
            'qbk_hash' => $this->qbk_hash
        );
    }

    function load_boostbook_data($values, $refresh = false) {
        assert($this->dir_location || $refresh);
        assert(!$this->loaded);

        $this->title_xml = BoostSiteTools::trim_lines($values['title_xhtml']);
        $this->purpose_xml = BoostSiteTools::trim_lines($values['purpose_xhtml']);
        $this->notice_xml = BoostSiteTools::trim_lines($values['notice_xhtml']);
        $this->notice_url = $values['notice_url'];

        $this->pub_date = $values['pub_date'];
        $this->id = $values['id'];
        if (!$this->id) {
            $this->id = strtolower(preg_replace('@[\W]@', '_', $this->title_xml));
        }
        if ($this->dir_location) {
            $this->location = $this->dir_location . $this->id . '.html';
            $this->dir_location = null;
            $this->page_state = null;
        }

        $this->loaded = true;

        $doc_prefix  = null;
        if ($this->get_release_status() !== 'released' && $this->get_documentation()) {
            $doc_prefix = rtrim($this->get_documentation(), '/');
            $values['description_xhtml'] = BoostSiteTools::transform_links($values['description_xhtml'],
                function ($x) use ($doc_prefix) {
                    return preg_match('@^/(?:libs/|doc/html/)@', $x)
                        ? $doc_prefix.$x : $x;
                });
        }

        $version = $this->array_get($this->release_data, 'version');
        if ($version && $doc_prefix) {
            $version = BoostVersion::from($version);
            $final_documentation = "/doc/libs/{$version->final_doc_dir()}";
            $link_pattern = '@^'.preg_quote($final_documentation, '@').'/@';
            $replace = "{$doc_prefix}/";
            $values['description_xhtml'] = BoostSiteTools::transform_links($values['description_xhtml'],
                function($x) use($link_pattern, $replace) {
                    return preg_replace($link_pattern, $replace, $x);
                });
        }

        $this->description_xml = BoostSiteTools::trim_lines($values['description_xhtml']);
    }

    function full_title_xml() {
        if ($this->type !== 'release') { return $this->title_xml; }
        switch($this->get_release_status()) {
        case 'released':
            return $this->title_xml;
        case 'beta':
            return "{$this->title_xml} beta";
        default:
            return "{$this->title_xml} - work in progress";
        }
    }

    function web_date() {
        if (!$this->pub_date) {
            return 'In Progress';
        } else {
            return gmdate('F jS, Y H:i', $this->pub_date->getTimestamp()).' GMT';
        }
    }

    function download_table_data() {
        if (!$this->release_data) { return null; }
        $downloads = $this->array_get($this->release_data, 'downloads');
        $signature = $this->array_get($this->release_data, 'signature');
        $third_party = $this->array_get($this->release_data, 'third_party');
        if (!$downloads && !$third_party) { return $this->get_download_page(); }

        $tabled_downloads = array();
        foreach ($downloads as $download) {
            // Q: Good default here?
            $line_endings = $this->array_get($download, 'line_endings', 'unix');
            unset($download['line_endings']);
            $tabled_downloads[$line_endings][] = $download;
        }

        $result = array(
            'downloads' => $tabled_downloads
        );
        if ($signature) { $result['signature'] = $signature; }
        if ($third_party) { $result['third_party'] = $third_party; }

        return $result;
    }

    function download_table() {
        // TODO: Removing this temporarily so I can add the download links
        //       without putting the release notes on the front page.
        //       Might remove this code permananently, I'm not sure if it
        //       does any good.
        //if ($this->type == 'release' && (!$this->release_status || $this->release_status === 'dev')) {
        //    return '';
        //}

        $downloads = $this->download_table_data();

        if (is_array($downloads)) {
            # Print the download table.

            $hash_column = false;
            foreach($downloads['downloads'] as $x) {
                foreach($x as $y) {
                    if (array_key_exists('sha256', $y)) {
                        $hash_column = true;
                    }
                }
            }

            $output = '';
            $output .= '              <table class="download-table">';
            if ($this->get_release_status() === 'beta') {
                $output .= '<caption>Beta Downloads</caption>';
            } else {
                $output .= '<caption>Downloads</caption>';
            }
            $output .= '<tr><th scope="col">Platform</th><th scope="col">File</th>';
            if ($hash_column) {
                $output .= '<th scope="col">SHA256 Hash</th>';
            }
            $output .= '</tr>';

            foreach (array('unix', 'windows') as $platform) {
                $platform_downloads = $downloads['downloads'][$platform];
                $output .= "\n";
                $output .= '<tr><th scope="row"';
                if (count($platform_downloads) > 1) {
                    $output .= ' rowspan="'.count($platform_downloads).'"';
                }
                $output .= '>'.html_encode($platform).'</th>';
                $first = true;
                foreach ($platform_downloads as $download) {
                    if (!$first) { $output .= '<tr>'; }
                    $first = false;

                    $file_name = basename(parse_url($download['url'], PHP_URL_PATH));

                    $output .= '<td><a href="';
                    if (strpos($download['url'], 'sourceforge') !== false) {
                        // TODO: I used to add '/download' to source links,
                        //       but that doesn't seem to be needed any more...
                        //$output .= html_encode("{$download['url']}/download");
                        $output .= html_encode($download['url']);
                    }
                    else {
                        $output .= html_encode($download['url']);
                    }
                    $output .= '">';
                    $output .= html_encode($file_name);
                    $output .= '</a></td>';
                    if ($hash_column) {
                        $output .= '<td>';
                        $output .= html_encode($this->array_get($download, 'sha256'));
                        $output .= '</td>';
                    }
                    $output .= '</tr>';
                }
            }

            $output .= '</table>';

            if (array_key_exists('signature', $downloads)) {
                $output .= "<p><a href='/".html_encode($downloads['signature']['location']).
                    "'>List of checksums</a> signed by ".
                    "<a href='".html_encode($downloads['signature']['key'])."'>".
                    html_encode($downloads['signature']['name'])."</a></p>\n";
            }

            if (array_key_exists('third_party', $downloads)) {
                $output .= "\n";
                $output .= "<h3>Third Party Downloads</h3>\n";
                $output .= "<ul>\n";
                foreach($downloads['third_party'] as $download) {
                    $output .= '<li>';
                    $output .= '<a href="'.html_encode($download['url']).'">';
                    $output .= html_encode($download['title']);
                    $output .= '</a>';
                    $output .= "</li>\n";
                }
                $output .= "</ul>\n";
            }

            return $output;
        } else if (is_string($downloads)) {
            # If the link didn't match the normal version number pattern
            # then just use the old fashioned link to sourceforge. */

            $output = '              <p><span class="news-download"><a href="'.
                html_encode($downloads).'">';

            switch($this->get_release_status()) {
            case 'released':
                $output .= 'Download this release.';
                break;
            case 'beta':
                $output .= 'Download this beta release.';
                break;
            case 'dev':
                $output .= 'Download snapshot.';
                break;
            default:
                assert(false);
            }

            $output .= '</a></span></p>';

            return $output;
        }
        else {
            return '';
        }
    }

    function is_published($state = null) {
        if ($this->page_state == 'new') {
            return false;
        }
        if (!is_null($state) && $this->get_release_status() !== $state) {
            return false;
        }
        return true;
    }

    function get_release_status() {
        return $this->array_get($this->release_data, 'release_status');
    }

    function get_documentation() {
        return $this->array_get($this->release_data, 'documentation');
    }

    function get_download_page() {
        return $this->array_get($this->release_data, 'download_page');
    }

    function array_get($array, $key, $default = null) {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}
