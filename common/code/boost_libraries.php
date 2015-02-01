<?php
/*
  Copyright 2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

// Change this when developing.
define('USE_SERIALIZED_INFO', true);
require_once(dirname(__FILE__) . '/url.php');

/**
 * Stores the details of all the boost libraries organised by version.
 *
 * This is pretty awkward as there are a few different representions here
 * and it's quite easy to get confused between them. Maybe needs to be split
 * up into a couple of different classes.
 */
class BoostLibraries
{
    private $categories = array();
    private $db = array();

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
    static function from_xml($xml, $info = null)
    {
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        if (!xml_parse_into_struct($parser, $xml, $values)) {
            die("Error parsing XML");
        }
        xml_parser_free($parser);

        ##print '<!-- '; print_r($values); print ' -->';
        
        $categories = array();
        $category = NULL;
        $libs = array();
        $lib = NULL;

        foreach ( $values as $key => $val )
        {
            if ($val['tag'] == 'boost' || $val['tag'] == 'categories')
            {
                // Ignore boost tags.
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
                    case 'module':
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
                    case 'std-proposal':
                    case 'std-tr1':
                    {
                        $value = isset($val['value']) ? trim($val['value']) : false;
                        if($value && $value != 'true' && $value != 'false') {
                            echo 'Invalid value for ',html_encode($val['tag']),
                                ': ', $value, "\n";
                            exit(0);
                        }
                        $lib[$val['tag']] = ($value == 'true');
                    }
                    break;
                    case 'authors':
                    case 'maintainers':
                    case 'category':
                    case 'std':
                    {
                        if(isset($val['value'])) {
                            $name = trim($val['value']);
                            $lib[$val['tag']][] = $name;
                        }
                    }
                    break;
                    default:
                        echo 'Invalid tag: ', html_encode($val['tag']), "\n";
                        exit(0);
                }
            }
            else if ($val['tag'] == 'library' && $val['type'] == 'close' && $lib)
            {
                $libs[] = $lib;
                $lib = NULL;
            }
            else
            {
                echo 'Invalid tag: ', html_encode($val['tag']), "\n";
                exit(0);
            }
        }

        return new self($libs, $categories, $info);
    }

    static function from_json($json, $info = null)
    {
        $categories = array();
        $libs = array();
        $json = trim($json);

        $import = json_decode($json, true);
        if (!$import) {
            throw new library_decode_exception("Error decoding json.", $json);
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

        return new self($libs, $categories, $info);
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
    static function from_array($libs, $info = null)
    {
        return new self($libs, array(), $info);
    }

    /**
     *
     * @param array $libs Array of lib details, can contain multiple historical
     *      entries, using 'update-version' to indicate their historical order.
     * @param array $categories
     * @param array $info Optional info to use when creating libraries.
     *                    As in BoostLibrary, with additional field:
     *                    version = default update version
     */
    private function __construct(array $flat_libs, array $categories,
            $info = null)
    {
        $this->db = array();
        $this->categories = $categories;

        foreach ($flat_libs as $details) {
            $update_version =
                isset($details['update-version']) ? $details['update-version'] : (
                isset($info['version']) ? $info['version'] : (
                isset($details['boost-version']) ? $details['boost-version']
                    : null));
            if (!$update_version) {
                throw new BoostLibraries_exception(
                        "No version info for {$details['key']}");
            }
            $update_version = BoostVersion::from($update_version);
            if (isset($details['update-version'])) {
                unset($details['update-version']);
            }

            $lib = new BoostLibrary($details, $info);
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
     * Update the libraries from xml details.
     *
     * @param \BoostLibraries $update
     * @throws BoostLibraries_exception
     */
    public function update($update) {
        foreach($update->db as $key => $libs) {
            if (count($libs) > 1) {
                throw new BoostLibraries_exception("Duplicate key: {$key}\n");
            }

            $lib = reset($libs);
            $this->db[$key][(string) $lib->update_version] = $lib;
            $this->reduce_versions($key);
        }

        ksort($this->db);
    }

    public function update_for_release($version) {
        $version = BoostVersion::from($version);

        $libs = $this->get_for_version($version, null,
            'BoostLibraries::filter_all');
        foreach($libs as &$lib_details) {
            if (!isset($lib_details['boost-version'])) {
                $lib_details['boost-version'] = $version;
            }
        }
        unset($lib_details);

        $this->update(self::from_array($libs, array('version' => $version)));
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
            if ($last) {
                $current->fill_in_details_from_previous_version($last);
                if ($current->equal_to($last)) {
                    unset($this->db[$key][$version]);
                }
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

        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString('  ');

        $writer->startDocument('1.0', 'US-ASCII');
        $writer->startElement('boost');
        $writer->writeAttribute('xmlns:xsi',
                'http://www.w3.org/2001/XMLSchema-instance');

        if ($this->categories) {
            $writer->startElement('categories');
            foreach ($this->categories as $name => $category) {
                $writer->startElement('category');
                $writer->writeAttribute('name', $name);
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
                $details = self::clean_for_output($details);

                $writer->startElement('library');
                $this->write_element($writer, $exclude, $details, 'key');
                $this->write_element($writer, $exclude, $details, 'module');
                $this->write_optional_element($writer, $exclude, $details, 'boost-version');
                $this->write_optional_element($writer, $exclude, $details, 'update-version');
                $this->write_optional_element($writer, $exclude, $details, 'status');
                $this->write_optional_element($writer, $exclude, $details, 'name');
                $this->write_many_elements($writer, $exclude, $details, 'authors');
                $this->write_many_elements($writer, $exclude, $details, 'maintainers');
                $this->write_optional_element($writer, $exclude, $details, 'description');
                $this->write_optional_element($writer, $exclude, $details, 'documentation');
                $this->write_many_elements($writer, $exclude, $details, 'std');
                $this->write_many_elements($writer, $exclude, $details, 'category');
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
            else {
                $writer->writeElement($name, $lib[$name]);
            }
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
                $details = $lib->details;

                if (empty($details['std'])) {
                    unset($details['std']);
                }
                unset($details['std-tr1']);
                unset($details['std-proposal']);

                $details = self::clean_for_output($details, $exclude);

                foreach ($exclude as $field) {
                    if (isset($details[$field])) {
                        unset($details[$field]);
                    }
                }

                $export[] = $details;
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

    static function filter_released($x) {
        return $x['boost-version'];
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
                'BoostLibraries::filter_released' :
                'BoostLibraries::filter_all';
        }

        foreach($this->db as $key => $versions) {
            $details = null;

            foreach($versions as $lib) {
                if ($version->compare($lib->update_version) >= 0) {
                    $details = $lib->details;
                }
            }

            if ($details) {
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

        foreach($libs as $key => $library) {
            foreach($library['category'] as $category) {
                if(!isset($this->categories[$category])) {
                    echo 'Unknown category: ', html_encode($category), "\n";
                    exit(0);
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


    /**
     * Prepare library details for output.
     *
     * Currently just reduces the version information.
     *
     * @param array $lib
     * @return array Library details for output.
     */
    static function clean_for_output($lib) {
        //if (!isset($lib['update-version']) && !isset($lib['boost-version'])) {
        //    throw new RuntimeException("No version data for {$lib['name']}.");
        //}

        if (isset($lib['update-version'])) {
            $lib['update-version'] = (string) $lib['update-version'];
        }

        if (isset($lib['boost-version'])) {
            $lib['boost-version'] = (string) $lib['boost-version'];
        }

        if (isset($lib['boost-version']) && isset($lib['update-version']) &&
                $lib['update-version'] == $lib['boost-version']) {
            unset($lib['update-version']);
        }

        return $lib;
    }
}

class BoostLibraries_exception extends RuntimeException {}
class library_decode_exception extends BoostLibraries_exception {
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
