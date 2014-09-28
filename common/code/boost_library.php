<?php
/*
  Copyright 2006 Redshift Software, Inc.
  Copyright 2014 Daniel James
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

require_once(dirname(__FILE__) . '/url.php');

/**
 * The basic details about a single library.
 *
 * $info keys when creating:
 *      module  = module name
 *      path    = module path
 */
class BoostLibrary
{
    var $details = null;
    var $update_version = null; // Used by BoostLibraries.

    /**
     * Read a libraries json file, and return an array of BoostLibrary.
     */
    static function read_libraries_json($json, $info = array()) {
        $json = trim($json);
        $libs = json_decode($json, true);
        if (!$libs) {
            throw new library_decode_exception("Error decoding json.", $json);
        }
        if ($json[0] == '{') {
            $libs = array($libs);
        }
        return array_map(
            function($lib) { return new BoostLibrary($lib, $info); }, $libs);
    }

    public function __construct($lib, $info) {
        assert(!isset($lib['update-version']));
        assert(isset($lib['key']));
        assert(isset($info['module']) == isset($info['path']));

        if (!empty($lib['boost-version'])) {
            $lib['boost-version']
                    = BoostVersion::from($lib['boost-version']);
        }

        if (isset($info['module'])) {
            assert(!isset($lib['module']));
            $lib['module'] = $info['module'];
            $documentation_url =
                isset($lib['documentation']) ? $lib['documentation'] : '.';
            $lib['documentation'] =
                ltrim(resolve_url($documentation_url, trim($info['path'], '/').'/'), '/');
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

        // Normalize the data representation
        foreach($lib as $key => &$value) {
            if (is_string($value)) {
                $value = trim(preg_replace('@\s+@', ' ', $value));
            }
        }
        if (!empty($lib['category'])) { sort($lib['category']); }

        $this->details = $lib;
    }

    /** Kind of hacky way to fill in details that probably shouldn't be
     *  stored here anyway. */
    public function fill_in_details_from_previous_version($previous) {
        if (!isset($this->details['boost-version'])
                && isset($previous->details['boost-version'])) {
            $this->details['boost-version'] = $previous->details['boost-version'];
        }
    }

    public function equal_to($other) {
        $details1 = $this->details;
        $details2 = $other->details;

        if (count(array_diff_key($details1, $details2))
                || count(array_diff_key($details2, $details1))) {
            return false;
        }

        foreach($details1 as $key => $value) {
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
     * Convert authors and maintainers to strings.
     * This is kind of rubbish, but I want authors and maintainers to be
     * arrays in the repo metadata, but strings on the website. So call this
     * when creating the website file.
     */
    public function squash_name_arrays() {
        if (isset($this->details['authors']))
        {
            $this->details['authors']
                    = $this->names_to_string($this->details['authors']);
        }

        if (isset($this->details['maintainers']))
        {
            $this->details['maintainers']
                    = $this->names_to_string($this->details['maintainers']);
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
}
