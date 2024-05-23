<?php
# Copyright 2011, 2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)

class BoostPages {
    // If you change these values, they will only apply to new pages.
    var $page_locations = array(
        array(
            'source' => 'feed/history/*.qbk',
            'destination' => 'users/history',
            'section' => 'history'
        ),
        array(
            'source' => 'feed/news/*.qbk',
            'destination' => 'users/news',
            'section' => 'news',
        ),
        array(
            'source' => 'feed/downloads/*.qbk',
            'destination' => 'users/download',
            'section' => 'downloads'
        ),
    );

    var $root;
    var $hash_file;
    var $page_cache_file;
    var $beta_release_notes_file;
    var $pages = Array();
    var $page_cache = Array();
    var $beta_release_notes = Array();
    var $releases = null;

    function __construct($root = null, $paths = array()) {
        if (!$root) { $root = BOOST_WEBSITE_DATA_ROOT_DIR; }
        $hash_file = BoostWebsite::array_get($paths, 'hash_file', "generated/state/feed-pages.txt");
        $page_cache = BoostWebsite::array_get($paths, 'page_cache', "generated/state/page-cache.txt");
        $beta_release_notes = BoostWebsite::array_get($paths, 'beta_release_notes', "generated/state/beta_release_notes.txt");
        $release_file = BoostWebsite::array_get($paths, 'release_file', "generated/state/release.txt");

        $this->root = $root;
        $this->hash_file = "{$root}/{$hash_file}";
        $this->page_cache_file = "{$root}/{$page_cache}";;
        $this->beta_release_notes_file = "{$root}/{$beta_release_notes}";;
        $this->releases = new BoostReleases("{$root}/{$release_file}");

        if (is_file($this->hash_file)) {
            foreach(BoostState::load($this->hash_file) as $qbk_file => $record) {
                if (!isset($record['section'])) {
                    $location_data = $this->get_page_location_data($qbk_file);
                    $record['section'] = $location_data['section'];
                }
                $this->pages[$qbk_file]
                    = new BoostPages_Page($qbk_file,
                        $this->get_release_data($qbk_file, $record['section']),
                        $record);
            }
        }

        if (is_file($this->page_cache_file)) {
            $this->page_cache = BoostState::load($this->page_cache_file);
        }
        if (is_file($this->beta_release_notes_file)) {
            $this->beta_release_notes = BoostState::load($this->beta_release_notes_file);
        }
    }

    function save() {
        BoostState::save(
            array_map(function($page) { return $page->state(); }, $this->pages),
            $this->hash_file);
        BoostState::save($this->page_cache,  $this->page_cache_file);
        BoostState::save($this->beta_release_notes,  $this->beta_release_notes_file);
    }

    function reverse_chronological_pages() {
        $pages = $this->pages;

        $pub_date_order = array();
        $last_published_order = array();
        $unpublished_date = new DateTime("+10 years");
        foreach($pages as $index => $page) {
            $pub_date_order[$index] =
                ($page->release_data ?
                    BoostWebsite::array_get($page->release_data, 'release_date') :
                    null) ?:
                $page->index_info()->pub_date ?: $unpublished_date;
            $last_published_order[$index] = $page->last_modified;
        }
        array_multisort(
            $pub_date_order, SORT_DESC,
            $last_published_order, SORT_DESC,
            $pages);

        return $pages;
    }

    function scan_for_new_quickbook_pages() {
        foreach ($this->page_locations as $details) {
            foreach (glob("{$this->root}/{$details['source']}") as $qbk_file) {
                assert(strpos($qbk_file, $this->root) === 0);
                $qbk_file = substr($qbk_file, strlen($this->root) + 1);
                $this->update_qbk_file($qbk_file, $details['section']);
            }
        }
    }

    function get_page_location_data($qbk_path) {
        foreach ($this->page_locations as $details) {
            if (fnmatch($details['source'], $qbk_path)) {
                return $details;
            }
        }
        throw new BoostException("Unexpected quickbook file path: {$qbk_path}");
    }

    function get_release_data($qbk_file, $section) {
        if ($section != 'history' && $section != 'downloads') {
            return null;
        }

        $basename = pathinfo($qbk_file, PATHINFO_FILENAME);

        if (preg_match('@^([a-z](?:_[a-z]|[a-z0-9])*)_([0-9][0-9_]*)$@i', $basename, $match)) {
            return $this->releases->get_latest_release_data($match[1], $match[2]);
        }
        else {
            return null;
        }

    }

    function update_qbk_file($qbk_file, $section) {
        $record = null;

        if (!isset($this->pages[$qbk_file])) {
            $release_data = $this->get_release_data($qbk_file, $section);
            $record = new BoostPages_Page($qbk_file, $release_data);
            $record->section = $section;
            $this->pages[$qbk_file] = $record;
        } else {
            $record = $this->pages[$qbk_file];
        }

        switch($record->get_release_status()) {
        case 'released':
        case null:
            $qbk_hash = $this->calculate_qbk_hash($record, $section);

            if ($record->qbk_hash != $qbk_hash && $record->page_state != 'new') {
                $record->page_state = 'changed';
            }
            break;
        case 'beta':
            $qbk_hash = $this->calculate_qbk_hash($record, $section);

            if ($record->qbk_hash != $qbk_hash && !$record->page_state) {
                $record->page_state = 'release-data-changed';
            }
            break;
        case 'dev':
            // Not building anything for dev entries (TODO: Maybe delete a page
            // if it exists??? Not sure how that would happen).
            break;
        default:
            // Unknown release status.
            assert(false);
        }

        if ($record->page_state) {
            $record->section = $section;
            if ($record->qbk_hash != $qbk_hash) {
                $record->qbk_hash = $qbk_hash;
                $record->last_modified = new DateTime();
            }
        }
    }

    function calculate_qbk_hash($record, $section) {
        switch($record->get_release_status()) {
        case 'beta':
            // For beta files, don't hash the page source as we don't want to
            // rebuild when it updates.
            $context = hash_init('sha256');
            hash_update($context, json_encode($this->normalize_release_data(
                $record->release_data, $record->qbk_file, $section)));
            return hash_final($context);
        default:
            $context = hash_init('sha256');
            hash_update($context, json_encode($this->normalize_release_data(
                $record->release_data, $record->qbk_file, $section)));
            hash_update($context, str_replace("\r\n", "\n",
                file_get_contents("{$this->root}/{$record->qbk_file}")));
            return hash_final($context);
        }
    }

    // Make the release data look like it used to look in order to get a consistent
    // hash value. Pretty expensive, but saves constant messing around with hashes.
    private function normalize_release_data($release_data, $qbk_file, $section) {
        if (is_null($release_data)) { return null; }

        // Note that this can be determined from the quickbook file, so if
        // there's someway that it could change, then either qbk_hash or the
        // path would change anyway.
        unset($release_data['release_name']);

        // Fill in default values.
        $release_data += array(
                'release_notes' => $qbk_file, 'release_status' => 'released',
                'version' => '', 'documentation' => null, 'download_page' => null);

        // Release date wasn't originally included in data for old versions,
        // and shouldn't be changed, so easiest to ignore it.
        if (array_key_exists('release_date', $release_data) && (
                $section == 'downloads' ||
                !$release_data['version'] ||
                $release_data['version']->compare('1.62.0') <= 0))
        {
            unset($release_data['release_date']);
        }

        // Arrange the keys in order.
        $release_data = $this->arrange_keys($release_data, array(
            'release_notes', 'release_status', 'version', 'documentation',
            'download_page', 'downloads', 'signature', 'third_party'));

        // Replace version object with version string.
        if (array_key_exists('version', $release_data)) {
            $release_data['version'] = (string) $release_data['version'];
        }

        // Turn the downloads and third party downloads into numeric arrays.
        if (array_key_exists('downloads', $release_data)) {
            $new_downloads = $this->arrange_keys($release_data['downloads'],
                array('bz2', 'gz', '7z', 'exe', 'zip'));
            foreach($new_downloads as &$record) { krsort($record); }
            unset($record);
            $release_data['downloads'] = array_values($new_downloads);
        }
        if ($release_data && array_key_exists('third_party', $release_data)) {
            $release_data['third_party'] = array_values($release_data['third_party']);
        }
        if ($release_data && array_key_exists('signature', $release_data)) {
            $release_data['signature'] = $this->arrange_keys($release_data['signature'], array(
                'location', 'name', 'key'));
        }

        // Normalize fields with special value types.
        foreach ($release_data as $key => $value) {
            if (is_object($value)) {
                if ($value instanceof DateTime || $value instanceof DateTimeInterface) {
                    $release_data[$key] = $value->format(DateTime::ATOM);
                } else {
                    assert(false);
                }
            }
        }

        return $release_data;
    }

    private function arrange_keys($array, $key_order) {
        $key_order = array_flip($key_order);
        $key_sort1 = array();
        $key_sort2 = array();
        foreach ($array as $key => $data) {
            $key_sort1[$key] = array_key_exists($key, $key_order) ? $key_order[$key] : 999;
            $key_sort2[$key] = $key;
        }
        array_multisort($key_sort1, SORT_ASC, $key_sort2, SORT_ASC, $array);
        return $array;
    }

    function convert_quickbook_pages($mode = 'update') {
        $have_quickbook = false;

        if (BOOST_QUICKBOOK_EXECUTABLE) {
            try {
                BoostSuperProject::run_process(BOOST_QUICKBOOK_EXECUTABLE.' --version');
                $have_quickbook = true;
            }
            catch(ProcessError $e) {
                echo "Problem running quickbook, will not convert quickbook articles.\n";
            }
        } else {
            echo "BOOST_QUICKBOOK_EXECUTABLE is empty, will not convert quickbook articles.\n";
        }

        $in_progress_release_notes = array();
        $in_progress_failed = false;
        foreach ($this->pages as $page => $page_data) {
            if ($mode != 'refresh' && $page_data->dev_data) {
                $dev_page_data = clone($page_data);
                $dev_page_data->release_data = $dev_page_data->dev_data;
                $dev_page_data->page_state = 'changed';

                list($boostbook_values, $fresh_cache) = $this->load_quickbook_page($page, $have_quickbook);

                if (!$boostbook_values) {
                    echo "Unable to generate 'In Progress' entry for {$page}.\n";
                    $in_progress_failed = true;
                }
                else {
                    $in_progress_release_notes[] = array(
                        'full_title_xml' => $boostbook_values['title_xml'],
                        'web_date' => 'In Progress',
                        'download_table' => $dev_page_data->download_table(),
                        'description_xml' => $this->transform_page_html($dev_page_data, $boostbook_values['description_xhtml']),
                    );
                }
            }

            if ($mode == 'refresh') {
                // Refresh: Rebuild pages.
                $boostbook_values = null;
                switch ($page_data->get_release_status()) {
                case 'released':
                case null:
                    $boostbook_values = BoostWebsite::array_get($this->page_cache, $page);
                    if (!$boostbook_values && $have_quickbook) {
                        $boostbook_values = $this->load_quickbook_page_impl($page);
                    }
                    break;
                case 'beta':
                    $boostbook_values = BoostWebsite::array_get($this->beta_release_notes, "{$page}:{$page_data->release_data['version']}");
                    break;
                case 'dev':
                    break;
                default:
                    assert(false);
                }

                if (!$boostbook_values) {
                    echo "Unable to generate page for {$page}.\n";
                }
                else {
                    $this->generate_quickbook_page($page_data, $boostbook_values);
                }
            }
            else if ($mode == 'update' && $page_data->page_state === 'release-data-changed') {
                // Release data changed: Update only the release data,
                // otherwise use existing data.
                assert($page_data->get_release_status() == 'beta');
                $boostbook_values = BoostWebsite::array_get($this->beta_release_notes,
                    "{$page}:{$page_data->release_data['version']}");
                if (!$boostbook_values) {
                    echo "No beta cache entry for {$page}.\n";
                }
                else {
                    $this->generate_quickbook_page($page_data, $boostbook_values);
                    $page_data->page_state = null;
                    ++$page_data->update_count;
                }
            }
            else if ($mode == 'update' && $page_data->page_state) {
                list($boostbook_values, $fresh_cache) = $this->load_quickbook_page($page, $have_quickbook);

                if (!$boostbook_values) {
                    echo "Unable to generate page for {$page}.\n";
                }
                else {
                    if (!$fresh_cache) {
                        // If we have a dated cache entry, and aren't able to
                        // rebuild it, continue using the current entry, but
                        // don't change the page state - it will try
                        // again on the next run.
                        echo "Using old cached entry for {$page}.\n";
                    }
                    $this->update_page_data_from_boostbook_values($page_data, $boostbook_values);
                    $this->generate_quickbook_page($page_data, $boostbook_values);
                    if ($fresh_cache) {
                        $page_data->page_state = null;
                        ++$page_data->update_count;
                        if ($page_data->get_release_status() == 'beta') {
                            $this->beta_release_notes["{$page}:{$page_data->release_data['version']}"] =
                                $boostbook_values;
                        }
                    }
                }
            }
        }

        if ($mode != 'refresh' && !$in_progress_failed) {
            $template_vars = array(
                'releases' => $in_progress_release_notes,
            );
            self::write_template(
                "{$this->root}/users/history/in_progress.html",
                __DIR__."/templates/in_progress.php",
                $template_vars);
        }
    }

    function load_quickbook_page($page, $have_quickbook) {
        // Hash the quickbook source
        $hash = hash('sha256', str_replace("\r\n", "\n",
            file_get_contents("{$this->root}/{$page}")));

        // Get the page from quickbook/read from cache
        $boostbook_values = BoostWebsite::array_get($this->page_cache, $page);
        $fresh_cache = $boostbook_values && $boostbook_values['hash'] === $hash;

        if ($have_quickbook && !$fresh_cache)
        {
            $boostbook_values = $this->load_quickbook_page_impl($page, $hash);
            $fresh_cache = true;
            $this->page_cache[$page] = $boostbook_values;
        }

        return array($boostbook_values, $fresh_cache);
    }

    function load_quickbook_page_impl($page, $hash = null) {
        // Hash the quickbook source
        if (is_null($hash)) {
            $hash = hash('sha256', str_replace("\r\n", "\n",
                file_get_contents("{$this->root}/{$page}")));
        }

        $bb_parser = new BoostBookParser();

        $xml_filename = tempnam(sys_get_temp_dir(), 'boost-qbk-');
        try {
            echo "Converting ", $page, ":\n";
            BoostSuperProject::run_process(BOOST_QUICKBOOK_EXECUTABLE." --output-file {$xml_filename} -I {$this->root}/feed {$this->root}/{$page}");
            $values = $bb_parser->parse($xml_filename);
            $boostbook_values = array(
                'hash' => $hash,
                'title_xml' => BoostSiteTools::trim_lines($values['title_xhtml']),
                'purpose_xml' => BoostSiteTools::trim_lines($values['purpose_xhtml']),
                'notice_xml' => BoostSiteTools::trim_lines($values['notice_xhtml']),
                'notice_url' => $values['notice_url'],
                'pub_date' => $values['pub_date'],
                'id' => $values['id'],
                'description_xhtml' => BoostSiteTools::trim_lines($values['description_xhtml']),
            );
        } catch (Exception $e) {
            unlink($xml_filename);
            throw $e;
        }
        unlink($xml_filename);
        return $boostbook_values;
    }

    function update_page_data_from_boostbook_values($page_data, $boostbook_values) {
        $page_data->load_boostbook_data($boostbook_values);

        // Set the path where the page should be built.
        // This can only be done after the quickbook file has been converted,
        // as the page id is based on the file contents.

        if (!$page_data->location) {
            $location_data = $this->get_page_location_data($page_data->qbk_file);
            $page_data->location = "{$location_data['destination']}/{$page_data->id}.html";
            $page_data->guid = "https://www.boost.org/{$page_data->location}";
        }
    }

    function transform_page_html($page_data, $description_xhtml) {
        // Transform links in description

        if (($page_data->get_release_status() === 'dev' ||
            $page_data->get_release_status() === 'beta') &&
            $page_data->get_documentation()
        ) {
            $doc_prefix = rtrim($page_data->get_documentation(), '/');
            $description_xhtml = BoostSiteTools::transform_links_regex($description_xhtml,
                '@^(?=/libs/|/doc/html/)@', $doc_prefix);

            $version = BoostWebsite::array_get($page_data->release_data, 'version');
            if ($version && $version->is_numbered_release()) {
                $final_documentation = "/doc/libs/{$version->final_doc_dir()}";
                $description_xhtml = BoostSiteTools::transform_links_regex($description_xhtml,
                    '@^'.preg_quote($final_documentation, '@').'(?=/)@', $doc_prefix);
            }
        }

        return BoostSiteTools::trim_lines($description_xhtml);
    }

    function generate_quickbook_page($page_data, $boostbook_values) {
        $template_vars = array(
            'history_style' => '',
            'full_title_xml' => $page_data->full_title_xml($boostbook_values['title_xml']),
            'title_xml' => $boostbook_values['title_xml'],
            'note_xml' => '',
            'web_date' => $page_data->web_date($boostbook_values['pub_date']),
            'documentation_para' => '',
            'download_table' => $page_data->download_table(),
            'description_xml' => $this->transform_page_html($page_data, $boostbook_values['description_xhtml']),
        );

        if ($page_data->get_documentation()) {
            $template_vars['documentation_para'] = '              <p><a href="'.html_encode($page_data->get_documentation()).'">Documentation</a>';
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
    // Path to quickbook file
    var $qbk_file;

    // Page state
    var $section, $page_state, $location, $guid, $id, $last_modified;
    var $qbk_hash, $update_count;

    // Boostbook data stored for use in indexes etc.
    // These members should only be used when generating index pages,
    // otherwise use the boostbook data directly.
    private $title_xml, $purpose_xml, $notice_xml, $notice_url, $pub_date;

    // Extra state data that isn't saved.
    var $is_release = false;  // Is this a release?
    var $release_data = null; // Status of release where appropriate.
    var $dev_data = null;     // Status of release in development.

    function __construct($qbk_file, $release_data = null, $attrs = array()) {
        $this->qbk_file = $qbk_file;
        if ($release_data) {
            $this->is_release = true;
            $this->release_data = BoostWebsite::array_get($release_data, 'release');
            $this->dev_data = BoostWebsite::array_get($release_data, 'dev');
        }

        $this->section = BoostWebsite::array_get($attrs, 'section');
        $this->page_state = BoostWebsite::array_get($attrs, 'page_state');
        $this->location = BoostWebsite::array_get($attrs, 'location');
        $this->guid = BoostWebsite::array_get($attrs, 'guid');
        $this->id = BoostWebsite::array_get($attrs, 'id');
        $this->title_xml = BoostWebsite::array_get($attrs, 'title');
        $this->purpose_xml = BoostWebsite::array_get($attrs, 'purpose');
        $this->notice_xml = BoostWebsite::array_get($attrs, 'notice');
        $this->notice_url = BoostWebsite::array_get($attrs, 'notice_url');
        $this->last_modified = BoostWebsite::array_get($attrs, 'last_modified');
        $this->pub_date = BoostWebsite::array_get($attrs, 'pub_date');
        $this->qbk_hash = BoostWebsite::array_get($attrs, 'qbk_hash');
        $this->update_count = BoostWebsite::array_get($attrs, 'update_count', 0);

        // Ensure that pub_date as last_modified are DateTimes.
        // TODO: Probably not needed any more.
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

        if (!$this->guid && $this->location) {
            $this->guid = "http://www.boost.org/{$this->location}";
        }
    }

    function state() {
        return array(
            'section' => $this->section,
            'page_state' => $this->page_state,
            'location' => $this->location,
            'guid'  => $this->guid,
            'id'  => $this->id,
            'title' => $this->title_xml,
            'purpose' => $this->purpose_xml,
            'notice' => $this->notice_xml,
            'notice_url' => $this->notice_url,
            'last_modified' => $this->last_modified,
            'pub_date' => $this->pub_date,
            'qbk_hash' => $this->qbk_hash,
            'update_count' => $this->update_count,
        );
    }

    function load_boostbook_data($values) {
        $this->title_xml = $values['title_xml'];
        $this->purpose_xml = $values['purpose_xml'];
        $this->notice_xml = $values['notice_xml'];
        $this->notice_url = $values['notice_url'];
        $this->pub_date = $values['pub_date'];
        $this->id = $values['id'];
        if (!$this->id) {
            $this->id = strtolower(preg_replace('@[\W]@', '_', $this->title_xml));
        }
    }

    function index_info() {
        return (object) array(
            'id' => $this->id,
            'location' => $this->location,
            'guid' => $this->guid,
            'full_title_xml' => $this->full_title_xml($this->title_xml),
            'web_date' => $this->web_date($this->pub_date),
            'download_page' => $this->get_download_page(),
            'download_table' => $this->download_table(),
            'documentation' => $this->get_documentation(),
            'title_xml' => $this->title_xml,
            'purpose_xml' => $this->purpose_xml,
            'notice_xml' => $this->notice_xml,
            'notice_url' => $this->notice_url,
            'pub_date' => $this->pub_date,
        );
    }

    function full_title_xml($title_xml) {
        switch($this->get_release_status()) {
        case 'beta':
            return trim("{$title_xml} beta {$this->release_data['version']->beta_number()}");
        case 'dev':
            return "{$title_xml} - work in progress";
        case 'released':
        case null:
            return $title_xml;
        default:
            assert(false);
        }
    }

    function web_date($pub_date) {
        $date = null;

        if (!is_null($this->release_data)) {
            // For releases, use the release date, not the pub date
            if (array_key_exists('release_date', $this->release_data)) {
                $date = $this->release_data['release_date'];
            }
        }
        else {
            $date = $pub_date;
        }

        return $date ? gmdate('F jS, Y H:i', $date->getTimestamp()).' GMT' :
            'In Progress';
    }

    function download_table_data() {
        if (is_null($this->release_data)) { return null; }
        $downloads = BoostWebsite::array_get($this->release_data, 'downloads');
        $signature = BoostWebsite::array_get($this->release_data, 'signature');
        $third_party = BoostWebsite::array_get($this->release_data, 'third_party');
        if (!$downloads && !$third_party) { return $this->get_download_page(); }

        $tabled_downloads = array();
        foreach ($downloads as $download) {
            // Q: Good default here?
            $line_endings = BoostWebsite::array_get($download, 'line_endings', 'unix');
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
            switch($this->get_release_status()) {
            case 'released':
                $output .= '<caption>Downloads</caption>';
                break;
            case 'beta':
                $output .= '<caption>Beta Downloads</caption>';
                break;
            case 'dev':
                $output .= '<caption>Development Downloads</caption>';
                break;
            default:
                assert(false);
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
                        $output .= html_encode(BoostWebsite::array_get($download, 'sha256'));
                        $output .= '</td>';
                    }
                    $output .= '</tr>';
                }
            }

            $output .= '</table>';
            $output .= '* The download links are supported by grants from <a href="https://cppalliance.org/" target="_blank">The C++ Alliance</a>.<br><br>';

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
        if ($this->is_release && !$this->release_data) {
            return false;
        }
        if (!is_null($state) && $this->get_release_status() !== $state) {
            return false;
        }
        return true;
    }

    function get_release_status() {
        switch ($this->section) {
        case 'history':
            if (!$this->is_release) {
                return null;
            }

            if (!$this->release_data) {
                return 'dev';
            }

            if (array_key_exists('release_status', $this->release_data)) {
                return $this->release_data['release_status'];
            }

            if ($this->release_data['version']->is_numbered_release()) {
                return $this->release_data['version']->is_beta() ? 'beta' : 'released';
            }
            else {
                return 'dev';
            }
        case 'downloads':
            return 'released';
        default:
            return null;
        }
    }

    function get_documentation() {
        return is_null($this->release_data) ? null : BoostWebsite::array_get($this->release_data, 'documentation');
    }

    function get_download_page() {
        return is_null($this->release_data) ? null : BoostWebsite::array_get($this->release_data, 'download_page');
    }
}
