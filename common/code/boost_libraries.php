<?php
/*
  Copyright 2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

require_once(dirname(__FILE__) . '/boost_utility.php');
require_once(dirname(__FILE__) . '/boost_version.php');

class boost_libraries
{
    private $categories = array();
    private $db = array();

    function boost_libraries($xml_file)
    {
        $xml = implode("",file($xml_file));
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        if (!xml_parse_into_struct($parser, $xml, $values)) {
            die("Error parsing XML");
        }
        xml_parser_free($parser);

        ##print '<!-- '; print_r($values); print ' -->';
        
        $lib = NULL;
        $category = NULL;
        foreach ( $values as $key => $val )
        {
            if ($val['tag'] == 'category' && $val['type'] == 'open' && !$lib && !$category)
            {
                $category = isset($val['attributes']) ? $val['attributes'] : array();
            }
            else if($val['tag'] == 'title' && $category)
            {
                $category['title'] = isset($val['value']) ? trim($val['value']) : '';
            }
            else if ($val['tag'] == 'category' && $val['type'] == 'close' && $category)
            {
                $this->categories[$category['name']] = $category;
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
                    case 'authors':
                    case 'description':
                    case 'documentation':
                    case 'status':
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
                    case 'category':
                    {
                        if(isset($val['value'])) {
                            $name = trim($val['value']);
                            $lib['category'][] = $name;
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
                assert(isset($lib['boost-version']));
                assert(isset($lib['key']));

                if (!isset($lib['update-version'])) {
                    $lib['update-version'] = $lib['boost-version'];
                }

                $this->db[$lib['key']][] = $lib;
                $lib = NULL;
            }
        }

        foreach ($this->db as $key => &$libs) {
            usort($libs, function($x, $y) {
                return $x['update-version']->compare($y['update-version']);
            });
        }
    }
    
    /**
     * Generate an xml representation of the library data.
     *
     * @return string
     */
    function to_xml() {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString('  ');

        $writer->startDocument('1.0', 'US-ASCII');
        $writer->startElement('boost');
        $writer->writeAttribute('xmlns:xsi',
                'http://www.w3.org/2001/XMLSchema-instance');

        $writer->startElement('categories');
        foreach ($this->categories as $name => $category) {
            $writer->startElement('category');
            $writer->writeAttribute('name', $name);
            $writer->writeElement('title', $category['title']);
            $writer->endElement();
        }
        $writer->endElement(); // categories

        foreach ($this->db as $key => $libs) {
            foreach($libs as $lib) {
                $writer->startElement('library');
                $writer->writeElement('key', $lib['key']);
                $writer->writeElement('boost-version', $lib['boost-version']);
                if ($lib['update-version'] != $lib['boost-version']) {
                    $writer->writeElement('update-version', $lib['update-version']);
                }
                $this->write_optional_element($writer, $lib, 'status');
                $this->write_optional_element($writer, $lib, 'name');
                $this->write_optional_element($writer, $lib, 'authors');
                $this->write_optional_element($writer, $lib, 'description');
                $this->write_optional_element($writer, $lib, 'documentation');
                $this->write_optional_element($writer, $lib, 'std-proposal');
                $this->write_optional_element($writer, $lib, 'std-tr1');
                foreach($lib['category'] as $category) {
                    $writer->writeElement('category', $category);
                }
                $writer->endElement();
            }
        }

        $writer->endElement(); // boost
        $writer->endDocument();
        return $writer->outputMemory();
    }

    /**
     * Write a library element.
     *
     * @param XMLWriter $writer
     * @param string $lib
     * @param string $name
     */
    private function write_optional_element($writer, $lib, $name) {
        if (isset($lib[$name])) {
            $value = $lib[$name];
            $value = is_bool($value) ?
                    ($value ? "true" : "false") :
                    (string) $value;

            $writer->writeElement($name, $value);
        }
    }

    function get_for_version($version, $sort = null, $filter = null) {
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

    function get_categories() {
        return $this->categories;
    }
}
?>
