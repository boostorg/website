<?php
/*
  Copyright 2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

require_once(dirname(__FILE__) . '/boost_utility.php');
require_once(dirname(__FILE__) . '/boost_version.php');
require_once(dirname(__FILE__) . '/url.php');

/**
 * Stores the details of all the boost libraries organised by version.
 *
 * This is pretty awkward as there are a few different representions here
 * and it's quite easy to get confused between them. Maybe needs to be split
 * up into a couple of different classes.
 */
class boost_libraries
{
    private $categories = array();
    private $db = array();

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
     * @return \boost_libraries
     */
    static function from_xml($xml, $version = null)
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
                            echo 'Invalid value for ',htmlentities($val['tag']),
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
                        echo 'Invalid tag: ', htmlentities($val['tag']), "\n";
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
                echo 'Invalid tag: ', htmlentities($val['tag']), "\n";
                exit(0);
            }
        }

        return new self($libs, $categories, $version);
    }

    static function from_json($json, $version = null)
    {
        $categories = array();
        $libs = array();
        $json = trim($json);

        $import = json_decode($json, true);
        if (!$import) {
            // TODO: Proper error handling.
            echo "Error decoding json: $json\n";
            exit(0);
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

        return new self($libs, $categories, $version);
    }

    /**
     * Create from an array of library details.
     *
     * This is pretty hacky, I only added for a one-off function. If you want
     * to make use of it, it could probably do with some sanity checks to
     * make sure that the libraries are valid.
     *
     * @param array $libraries
     * @return \boost_libraries
     */
    static function from_array($libs)
    {
        return new self($libs, array());
    }

    /**
     *
     * @param array $libs Array of lib details, can contain multiple historical
     *      entries, using 'update-version' to indicate their historical order.
     * @param array $categories
     * @param BoostVersion $version Optional version to use when update-version
     *      is missing.
     */
    private function __construct(array $flat_libs, array $categories,
            $version = null)
    {
        if ($version) { $version = BoostVersion::from($version); }

        $this->db = array();
        $this->categories = $categories;

        foreach ($flat_libs as $lib) {
            assert(isset($lib['key']));

            if (isset($lib['boost-version'])) {
                $lib['boost-version']
                        = BoostVersion::from($lib['boost-version']);
            }

            if (isset($lib['update-version'])) {
                $lib['update-version']
                        = BoostVersion::from($lib['update-version']);
            }
            else if ($version) {
                $lib['update-version'] = $version;
            }
            else if (isset($lib['boost-version'])) {
                $lib['update-version'] = $lib['boost-version'];
            }
            else {
                throw new boost_libraries_exception(
                        "No version info for {$lib['key']}");
            }

            // Preserve the current empty authors tags.
            if (!isset($lib['authors'])) {
                $lib['authors'] = '';
            }

            if (!isset($lib['std'])) {
                $lib['std'] = array();
            }

            foreach(array('proposal', 'tr1') as $std) {
                $tag = "std-{$std}";

                if (isset($lib[$tag])) {
                    if ($lib[$tag]) {
                        $lib['std'][] = $std;
                    }
                    else {
                        $lib['std'] = array_diff($lib['std'], array($std));
                    }
                }
                else {
                    $lib[$tag] = in_array($std, $lib['std']);
                }
            }

            $lib['std'] = array_unique($lib['std']);

            $this->db[$lib['key']][(string) $lib['update-version']]
                    = self::normalize_spaces($lib);
        }

        ksort($this->db);

        foreach ($this->db as $key => $lib_entries) {
            $this->sort_versions($key);

            $boost_version = null;

            foreach (array_keys($lib_entries) as $version) {
                if (!$boost_version && isset($version['boost-version'])) {
                    $boost_version = $version['boost-version'];
                }

                if ($boost_version && !isset($version['boost-version'])) {
                    $version['boost-version'] = $boost_version;
                }

                sort($this->db[$key][$version]['category']);
            }
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
            foreach ($libs as $version => &$details) {
                if (isset($details['authors']))
                {
                    $details['authors']
                            = $this->names_to_string($details['authors']);
                }

                if (isset($details['maintainers']))
                {
                    $details['maintainers']
                            = $this->names_to_string($details['maintainers']);
                }
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
     * @param \boost_libraries $update
     * @param \BoostVersion $update_version The version of Boost that the
     *      xml describes.
     * @param type $module The module the xml is taken from.
     * @throws boost_libraries_exception
     */
    public function update($update, $module = null) {
        foreach($update->db as $key => $libs) {
            if (count($libs) > 1) {
                throw new boost_libraries_exception("Duplicate key: {$key}\n");
            }

            $details = reset($libs);

            if ($module) {
                if (!isset($details['module'])) {
                    $details['module'] = $module;
                }

                $details['documentation'] = resolve_url(
                        isset($details['documentation'])
                            ? $details['documentation'] : '.',
                        "/libs/{$details['module']}/");
                $details['documentation'] =
                        ltrim($details['documentation'], '/');
            }

            $this->db[$key][(string) $details['update-version']] = $details;
            $this->reduce_versions($key);
        }

        ksort($this->db);
    }

    private function sort_versions($key) {
        uasort($this->db[$key], function($x, $y) {
            return $x['update-version']->compare($y['update-version']);
        });
    }

    private function reduce_versions($key) {
        $this->sort_versions($key);
        $last = null;

        foreach ($this->db[$key] as $version => $current) {
            if ($last) {
                if (!isset($current['boost-version'])
                        && isset($last['boost-version'])) {
                    $current['boost-version'] = $last['boost-version'];
                    $this->db[$key][$version] = $current;
                }

                if ($this->equal_details($last, $current)) {
                    unset($this->db[$key][$version]);
                }
            }

            $last = $current;
        }
    }

    private function equal_details($details1, $details2) {
        if (count(array_diff_key($details1, $details2))
                || count(array_diff_key($details2, $details1))) {
            return false;
        }

        foreach($details1 as $key => $value) {
            if ($key == 'update-version') continue;

            if (is_object($value)) {
                if ($value->compare($details2[$key]) != 0) return false;
            }
            else {
                if ($value != $details2[$key]) return false;
            }
        }

        return true;
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
                $lib = self::clean_for_output($lib);

                $writer->startElement('library');
                $this->write_element($writer, $exclude, $lib, 'key');
                $this->write_element($writer, $exclude, $lib, 'module');
                $this->write_optional_element($writer, $exclude, $lib, 'boost-version');
                $this->write_optional_element($writer, $exclude, $lib, 'update-version');
                $this->write_optional_element($writer, $exclude, $lib, 'status');
                $this->write_optional_element($writer, $exclude, $lib, 'name');
                $this->write_many_elements($writer, $exclude, $lib, 'authors');
                $this->write_many_elements($writer, $exclude, $lib, 'maintainers');
                $this->write_optional_element($writer, $exclude, $lib, 'description');
                $this->write_optional_element($writer, $exclude, $lib, 'documentation');
                $this->write_many_elements($writer, $exclude, $lib, 'std');
                $this->write_many_elements($writer, $exclude, $lib, 'category');
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
                if (empty($lib['std'])) {
                    unset($lib['std']);
                }
                unset($lib['std-tr1']);
                unset($lib['std-proposal']);

                $lib = self::clean_for_output($lib, $exclude);
                $lib = self::normalize_spaces($lib);

                foreach ($exclude as $field) {
                    if (isset($lib[$field])) {
                        unset($lib[$field]);
                    }
                }

                $export[] = $lib;
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
        return str_replace('\\/', '/', json_encode($export, JSON_PRETTY_PRINT));
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

        foreach($this->db as $key => $versions) {
            $lib = null;

            foreach($versions as $l) {
                if ($version->compare($l['update-version']) >= 0) {
                    $lib = $l;
                }
            }

            if ($lib) {
                if ($filter && !call_user_func($filter, $lib)) continue;

                $libs[$key] = $lib;
            }
        }

        $libs = array_values($libs);
        if($sort) {
            uasort($libs, sort_by_field($sort));
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
                    echo 'Unknown category: ', htmlentities($category), "\n";
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
     * @return array
     */
    function get_history($key) {
        return $this->db[$key];
    }

    /**
     * Normalize the spaces in string values of an array.
     */
    private static function normalize_spaces($lib) {
        foreach($lib as $key => &$value) {
            if (is_string($value)) {
                $value = trim(preg_replace('@\s+@', ' ', $value));
            }
        }

        return $lib;
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
        if (!isset($lib['update-version']) && !isset($lib['boost-version'])) {
            throw new RuntimeException("No version data for {$lib['name']}.");
        }

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

class boost_libraries_exception extends RuntimeException {}

?>
