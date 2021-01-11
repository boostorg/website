<?php
/*
  Copyright 2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/

// Change this when developing.
define('USE_SERIALIZED_INFO', true);

/**
 * Stores the details of all the boost libraries organised by version.
 *
 * This is pretty awkward as there are a few different representions here
 * and it's quite easy to get confused between them. Maybe needs to be split
 * up into a couple of different classes.
 */
class BoostLibraries
{
    public $categories = array();
    public $db = array();
    public $latest_version = null;

    /**
     *
     */

    static function load()
    {
        return USE_SERIALIZED_INFO ?
            unserialize(file_get_contents(dirname(__FILE__) . '/../../generated/libraries.txt')) :
            BoostLibraries::from_xml_file(dirname(__FILE__) . '/../../doc/libraries.xml');
    }

    /**
     * Read library details from an xml file.
     *
     * @param string $file_path
     * @return \BoostVersion
     */
    static function from_xml_file($file_path)
    {
        return self::from_xml(file_get_contents($file_path));
    }

    /**
     * Read library details from an xml string
     *
     * @param string $xml
     * @return \BoostLibraries
     */
    static function from_xml($xml)
    {
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        if (!xml_parse_into_struct($parser, $xml, $values)) {
            throw new BoostLibraries_DecodeException("Error parsing XML", $xml);
        }
        xml_parser_free($parser);

        ##print '<!-- '; print_r($values); print ' -->';
        
        $categories = array();
        $category = NULL;
        $libs = array();
        $lib = NULL;
        $latest_version = null;

        foreach ( $values as $key => $val )
        {
            if ($val['tag'] == 'boost' || $val['tag'] == 'categories')
            {
                // Ignore boost tags.
            }
            else if ($val['tag'] == 'latest' && $val['type'] == 'complete' && !$lib && !$category)
            {
                $latest_version = BoostVersion::from($val['attributes']['version']);
            }
            else if ($val['tag'] == 'category' && $val['type'] == 'open' && !$lib && !$category)
            {
                $category = isset($val['attributes']) ? $val['attributes'] : array();
            }
            else if($val['tag'] == 'title' && $category)
            {
                $category['title'] = isset($val['value']) ? trim($val['value']) : '';
            }
            else if ($val['tag'] == 'category' && $val['type'] == 'close' && $category)
            {
                $categories[$category['name']] = $category;
                $category = NULL;
            }
            else if ($val['tag'] == 'library' && $val['type'] == 'open')
            {
                $lib = array();
            }
            else if ($val['type'] == 'complete')
            {
                switch ($val['tag'])
                {
                    case 'key':
                    case 'name':
                    case 'description':
                    case 'documentation':
                    case 'status':
                    case 'library_path':
                    {
                        if (isset($val['value'])) { $lib[$val['tag']] = trim($val['value']); }
                        else { $lib[$val['tag']] = ''; }
                    }
                    break;
                    case 'boost-version':
                    case 'update-version':
                    {
                        if (isset($val['value'])) { $lib[$val['tag']] = BoostVersion::from($val['value']); }
                        else { $lib[$val['tag']] = ''; }
                    }
                    break;
                    case 'authors':
                    case 'maintainers':
                    case 'category':
                    {
                        if(isset($val['value'])) {
                            $name = trim($val['value']);
                            $lib[$val['tag']][] = $name;
                        }
                    }
                    break;
                    default:
                    throw new BoostLibraries_DecodeException(
                        "Invalid tag: {$val['tag']}", $xml);
                }
            }
            else if ($val['tag'] == 'library' && $val['type'] == 'close' && $lib)
            {
                $libs[] = $lib;
                $lib = NULL;
            }
            else
            {
                throw new BoostLibraries_DecodeException(
                    "Invalid tag: {$val['tag']}", $xml);
            }
        }

        return new self($libs, $categories, null, $latest_version);
    }

    static function from_json($json)
    {
        $categories = array();
        $libs = array();
        $json = trim($json);

        $import = json_decode($json, true);
        if (!$import) {
            throw new BoostLibraries_DecodeException("Error decoding json.", $json);
        }

        if ($json[0] == '{') {
            if (isset($import['categories']) || isset($import['libraries'])) {
                if (isset($import['categories'])) {
                    $categories = $import['categories'];
                }

                if (isset($import['libraries'])) {
                    $libs = $import['libraries'];
                }
            }
            else {
                $libs = array($import);
            }
        }
        else {
            $libs = $import;
        }

        return new self($libs, $categories);
    }

    /**
     * Create from an array of library details.
     *
     * This is pretty hacky, I only added for a one-off function. If you want
     * to make use of it, it could probably do with some sanity checks to
     * make sure that the libraries are valid.
     *
     * @param array $libraries
     * @return \BoostLibraries
     */
    static function from_array($libs, $version = null)
    {
        return new self($libs, array(), $version);
    }

    /**
     *
     * @param array $libs Array of lib details, can contain multiple historical
     *      entries, using 'update-version' to indicate their historical order.
     * @param array $categories
     * @param array $version Optional update version to use when version info
     *                       is missing.
     */
    private function __construct(array $flat_libs, array $categories,
            $version = null, $latest_version = null)
    {
        $this->db = array();
        $this->categories = array_change_key_case($categories);
        $this->latest_version = $latest_version;

        foreach ($flat_libs as $details) {
            $update_version =
                isset($details['update-version']) ? $details['update-version'] : (
                $version ? $version : (
                isset($details['boost-version']) ? $details['boost-version']
                    : null));
            $update_version = $update_version ?
                BoostVersion::from($update_version) :
                BoostVersion::unreleased();
            if (!$update_version->is_update_version()) {
                throw new BoostLibraries_Exception("No version info for {$details['key']}");
            }
            if (isset($details['update-version'])) {
                unset($details['update-version']);
            }

            $lib = new BoostLibrary($details);
            $lib->update_version = $update_version;
            $this->db[$details['key']][(string) $update_version] = $lib;
        }

        ksort($this->db);

        foreach (array_keys($this->db) as $key) {
            $this->sort_versions($key);
        }
    }

    /**
     * Convert authors and maintainers to strings.
     * This is kind of rubbish, but I want authors and maintainers to be
     * arrays in the repo metadata, but strings on the website. So call this
     * when creating the website file.
     */
    public function squash_name_arrays() {
        foreach ($this->db as $key => &$libs) {
            foreach ($libs as $version => $lib) {
                $lib->squash_name_arrays();
            }
        }
    }

    /**
     * @param array|string $names
     * @return string
     */
    private function names_to_string($names) {
        if (is_array($names)) {
            $last_name = array_pop($names);

            return $names ?
                    implode(', ', $names)." and {$last_name}" :
                    $last_name;
        }
        else {
            return $names;
        }
    }

    /**
     * Update the libraries from an array of BoostLibrary.
     *
     * @param array $update
     */
    public function update($update_version = null, $update = null) {
        $this->update_start($update_version);
        if ($update) { $this->update_libraries($update_version, $update); }
        $this->update_finish($update_version);
    }

    public function update_start($update_version = null) {
        if ($update_version) {
            $update_version = BoostVersion::from($update_version);

            // TODO: Support for deleted libraries:
            // $deleted_library = ????
            // foreach(array_keys($this->db) as $key) {
            //     $this->db[$key][(string) $update_version] = $deleted_library;
            // }
        }
    }

    public function update_libraries($update_version, $update) {
        if ($update_version) {
            $update_version = BoostVersion::from($update_version);
        }

        foreach($update as $lib) {
            $category = array_key_exists('category', $lib->details)
                ? array_map('strtolower', $lib->details['category']) : array();
            $invalid_categories = array_diff($category,
                array_keys($this->categories));
            $valid_categories = array_intersect($category,
                    array_keys($this->categories));
//            if ($invalid_categories) {
//                echo $lib->details['key'], ": Invalid categories: ",
//                   implode(', ', $invalid_categories), "\n"; 
//            }

            if (!$valid_categories) {
                $valid_categories = array('miscellaneous');
            }

            // Sort categories to normalize them.
            // TODO: Shouldn't really be setting this directly from here.
            sort($valid_categories);
            $lib->details['category'] = $valid_categories;

            if ($update_version) {
                $lib->update_version = $update_version;
            }

            $key = $lib->details['key'];
            $this->db[$key][(string) $lib->update_version] = $lib;
        }
    }

    public function update_finish($version = null) {
        if ($version) {
            $version = BoostVersion::from($version);
        }

        $this->clean_db();

        // If this is a release, then pull all the libraries back out,
        // and set their version when not available.
        //
        // Note: can only do this after 'clean_db' as that copies the
        // old release details into the new release.
        if ($version && $version->is_numbered_release()) {
            $libs = $this->get_for_version($version, null,
                'BoostLibraries::filter_all');
            $new_libs = array();
            foreach($libs as $lib_details) {
                if (BoostWebsite::array_get($lib_details, 'status') === 'unreleased') {
                    continue;
                }

                $lib_version = BoostVersion::from($lib_details['boost-version']);
                if ($version->release_stage() > $lib_version->release_stage())
                {
                    $lib_details['boost-version'] = $version;
                }

                $new_libs[] = new BoostLibrary($lib_details);
            }
            $this->update_libraries($version, $new_libs);
            $this->clean_db();

            // Also update latest version if appropriate.
            if (!$this->latest_version || $version->compare($this->latest_version) > 0) {
                $this->latest_version = $version;
            }
        }
    }

    private function clean_db() {
        foreach(array_keys($this->db) as $key) {
            $this->reduce_versions($key);
        }

        ksort($this->db);
    }

    private function sort_versions($key) {
        uasort($this->db[$key], function($x, $y) {
            return $x->update_version->compare($y->update_version);
        });
    }

    private function reduce_versions($key) {
        $this->sort_versions($key);
        $last = null;

        foreach ($this->db[$key] as $version => $current) {
            $current->fill_in_details_from_previous_version($last);
            if ($last && $current->equal_to($last)) {
                unset($this->db[$key][$version]);
            }
            $last = $current;
        }
    }

    /**
     * Generate an xml representation of the library data.
     *
     * @param array $exclude Fields to leave out of the library output
     * @return string
     */
    function to_xml($exclude = array()) {
        $exclude = array_flip($exclude);

        $writer = new BoostLibraries_XMLWriter();
        $writer->openMemory();
        //$writer->setIndent(true);
        //$writer->setIndentString('  ');

        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('boost');
        $writer->writeAttribute('xmlns:xsi',
                'http://www.w3.org/2001/XMLSchema-instance');

        if ($this->latest_version) {
            $writer->startElement('latest');
            $writer->writeAttribute('version', (string) $this->latest_version);
            $writer->endElement();
        }
        if ($this->categories) {
            $writer->startElement('categories');
            foreach ($this->categories as $name => $category) {
                $writer->startElement('category');
                $writer->writeAttribute('name', $category['name']);
                $writer->writeElement('title', $category['title']);
                $writer->endElement();
            }
            $writer->endElement(); // categories
        }

        foreach ($this->db as $key => $libs) {
            foreach($libs as $lib) {
                $details = $lib->details;
                if ($lib->update_version) {
                    $details['update-version'] = $lib->update_version;
                }
                $details = BoostLibrary::clean_for_output($details);

                $writer->startElement('library');
                $this->write_element($writer, $exclude, $details, 'key');
                $this->write_optional_element($writer, $exclude, $details, 'library_path');
                $this->write_optional_element($writer, $exclude, $details, 'boost-version');
                $this->write_optional_element($writer, $exclude, $details, 'update-version');
                $this->write_optional_element($writer, $exclude, $details, 'status');
                $this->write_optional_element($writer, $exclude, $details, 'name');
                $this->write_many_elements($writer, $exclude, $details, 'authors');
                $this->write_many_elements($writer, $exclude, $details, 'maintainers');
                $this->write_optional_element($writer, $exclude, $details, 'description');
                $this->write_optional_element($writer, $exclude, $details, 'documentation');
                $this->write_category_elements($writer, $exclude, $details, 'category');
                $writer->endElement();
            }
        }

        $writer->endElement(); // boost
        $writer->endDocument();
        return $writer->outputMemory();
    }

    /**
     * Write a library array.
     *
     * @param XMLWriter $writer
     * @param array $exclude
     * @param string $lib
     * @param string $name
     */
    private function write_many_elements($writer, $exclude, $lib, $name) {
        if (isset($lib[$name]) && !isset($exclude[$name])) {
            if (is_array($lib[$name])) {
                foreach($lib[$name] as $value) {
                    $writer->writeElement($name, $value);
                }
            }
            else if ($lib[$name]) {
                $writer->writeElement($name, $lib[$name]);
            }
        }
    }

    /**
     * Write an array of categories.
     *
     * @param XMLWriter $writer
     * @param array $exclude
     * @param string $lib
     * @param string $name
     */
    private function write_category_elements($writer, $exclude, $lib, $name) {
        if (isset($lib[$name]) && !isset($exclude[$name])) {
            if (is_array($lib[$name])) {
                foreach($lib[$name] as $value) {
                    $this->write_category_element($writer, $name, $value);
                }
            }
            else {
                $this->write_category_element($writer, $name, $lib[$name]);
            }
        }
    }

    private function write_category_element($writer, $name, $value) {
        $writer->writeElement($name,
            $this->categories[strtolower($value)]['name']);
    }

    /**
     * Write a library element.
     *
     * @param XMLWriter $writer
     * @param array $exclude
     * @param string $lib
     * @param string $name
     */
    private function write_element($writer, $exclude, $lib, $name) {
        if (!isset($exclude[$name])) {
            $value = $lib[$name];
            $value = is_bool($value) ?
                    ($value ? "true" : "false") :
                    (string) $value;

            $writer->writeElement($name, $value);
        }
    }

    /**
     * Write a library element.
     *
     * @param XMLWriter $writer
     * @param array $exclude
     * @param string $lib
     * @param string $name
     */
    private function write_optional_element($writer, $exclude, $lib, $name) {
        if (isset($lib[$name])) {
            $this->write_element($writer, $exclude, $lib, $name);
        }
    }

    /**
     * Generate a json representation of the library data.
     *
     * @param array $exclude Fields to leave out of the library output
     * @return string
     */
    function to_json($exclude = array()) {
        $export = array();
        foreach ($this->db as $libs) {
            foreach($libs as $lib) {
                $export[] = $lib->array_for_json($exclude);
            }
        }

        // Pick an export format depending on what data we have.
        if ($this->categories) {
            $export = Array(
                'categories' => $this->categories,
                'libraries' => $export,
            );
        }
        else if (count($export) == 1) {
            $export = $export[0];
        }

        // I'm not sure why php escapes slashes, but I don't want them so
        // I'll just zap them. Maybe stop doing that in the future.
        return str_replace('\\/', '/',
            json_encode($export,
                (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) |
                (defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0)
            ));
    }

    static function filter_visible($x) {
        if ($x['boost-version']->is_hidden()) { return false; }
        if (in_array(BoostWebsite::array_get($x, 'status'),
                array('hidden', 'unreleased', 'removed')))
        {
            return false;
        }
        return true;
    }

    static function filter_all($x) {
        return true;
    }

    /**
     * Get the library details for a particular release.
     *
     * @param \BoostVersion $version
     * @param string $sort Optional field used to sort the libraries.
     * @param callable $filter Optional filter function.
     * @return array
     */
    function get_for_version($version, $sort = null, $filter = null) {
        $version = BoostVersion::from($version);
        $libs = array();

        if (!$filter) {
            $filter = $version->is_numbered_release() ?
                'BoostLibraries::filter_visible' :
                'BoostLibraries::filter_all';
        }

        foreach($this->db as $key => $versions) {
            $details = null;

            foreach($versions as $lib) {
                if ($version->compare($lib->update_version) >= 0) {
                    $details = $lib->details;
                }
            }

            if ($details && BoostWebsite::array_get($details, 'status') != 'removed') {
                if ($filter && !call_user_func($filter, $details)) continue;
                $libs[$key] = $details;
            }
        }

        $libs = array_values($libs);
        if($sort) {
            uasort($libs, BoostUtility::sort_by_field($sort));
        }
        return $libs;
    }

    /**
     * Get the library details for a particular release, grouped into
     * categories.
     *
     * @param \BoostVersion $version
     * @param string $sort Optional field used to sort the libraries.
     * @param callable $filter Optional filter function.
     * @return array
     */
    function get_categorized_for_version($version, $sort = null, $filter = null) {
        $libs = $this->get_for_version($version, $sort, $filter);
        $categories = $this->categories;

        foreach(array_keys($categories) as $category) {
            $categories[$category]['libraries'] = array();
        }

        foreach($libs as $key => $library) {
            foreach($library['category'] as $category) {
                if(!isset($this->categories[$category])) {
                    throw new BoostLibraries_Exception('Unknown category: '.$category);
                }
                $categories[$category]['libraries'][] = $library;
            }
        }

        return $categories;
    }

    /**
     * Get a list of the different categories.
     *
     * @return string
     */
    function get_categories() {
        return $this->categories;
    }

    /**
     * Get the full history of a library.
     *
     * @param string $key
     * @return array of BoostLibrary
     */
    function get_history($key) {
        return $this->db[$key];
    }


}

// Simple class to write out XML as XMLWriter isn't always available.
// Implements a very limited subset of the commands.
class BoostLibraries_XMLWriter {
    var $text = '';
    var $in_element = false;
    var $element_stack = array();

    function openMemory() {
        $this->text = '';
    }

    function outputMemory($flush = true) {
        $x = $this->text;
        if ($flush) { $this->text = ''; }
        return $x;
    }

    function startDocument($version, $encoding) {
        assert($encoding === 'UTF-8');
        assert(!$this->text);
        assert(!$this->in_element);
        assert(!$this->element_stack);
        $this->write("<?xml version=\"{$version}\" encoding=\"{$encoding}\"?".">");
    }

    function endDocument() {
        assert(!$this->in_element);
        assert(!$this->element_stack);
        $this->write("\n");
    }

    function startElement($name) {
        $this->closeElementIfOpen();
        $this->startLine();
        $this->write("<{$name}");
        $this->in_element = true;
        $this->element_stack[] = $name;
    }

    function endElement() {
        if ($this->in_element) {
            $this->write('/>');
            $this->in_element = false;
            array_pop($this->element_stack);
        } else {
            assert($this->element_stack);
            $name = array_pop($this->element_stack);
            $this->startLine();
            $this->write("</${name}>");
        }
    }

    private function closeElementIfOpen() {
        if ($this->in_element) {
            $this->write('>');
            $this->in_element = false;
        }
    }

    function writeElement($name, $value) {
        $this->closeElementIfOpen();
        $this->startLine();
        $this->write("<{$name}>");
        $this->writeText($value);
        $this->write("</{$name}>");
    }

    function writeAttribute($name, $value) {
        assert($this->in_element);
        $this->write(" {$name}=\"");
        $this->writeText($value);
        $this->write("\"");
    }

    private function writeText($text) {
        $text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
        // This bizarre text converts all remaining non-ascii characters
        // to xml entities. There's probably a better way to do this.
        $text = preg_replace_callback('/[^\0-\x{80}]/u', function ($x) {
                $decimal_char = hexdec(bin2hex(iconv('utf-8', 'ucs-4', $x[0])));
                return "&#{$decimal_char};";
            }, $text);
        $this->write($text);
    }

    private function startLine() {
        $this->write("\n".str_repeat('  ', count($this->element_stack)));
    }

    private function write($x) {
        $this->text .= $x;
    }
}

class BoostLibraries_Exception extends BoostException {}
class BoostLibraries_DecodeException extends BoostLibraries_Exception {
    private $content = '';

    function __construct($message, $content) {
        parent::__construct($message);
        $this->content = $content;
    }

    public function content() {
        return $this->content;
    }
}

?>
