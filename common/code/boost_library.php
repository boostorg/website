<?php
/*
  Copyright 2006 Redshift Software, Inc.
  Copyright 2014 Daniel James
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/

/**
 * The basic details about a single library.
 */
class BoostLibrary
{
    var $details = null;
    var $update_version = null; // Used by BoostLibraries.

    /**
     * Read a libraries json file, and return an array of BoostLibrary.
     */
    static function read_libraries_json($json) {
        $json = trim($json);
        $libs = json_decode($json, true);
        if (!is_array($libs)) {
            throw new BoostLibraries_DecodeException("Error decoding json.", $json);
        }
        if ($json[0] == '{') {
            $libs = array($libs);
        }
        return array_map(function($lib) {
                return new BoostLibrary($lib);
            }, $libs);
    }

    static function get_libraries_json($libs, $exclude = array()) {
        $export = array_map(function($lib) use($exclude) {
            return $lib->array_for_json($exclude);
        }, $libs);

        if (count($export) == 1) { $export = reset($export); }

        // I'm not sure why php escapes slashes, but I don't want them so
        // I'll just zap them. Maybe stop doing that in the future.
        return str_replace('\\/', '/',
            json_encode($export,
                (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) |
                (defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0)
            ));
    }

    public function __construct($lib) {
        assert(!isset($lib['update-version']));
        assert(isset($lib['key']));

        // Convert version number to object
        if (!empty($lib['boost-version'])) {
            $lib['boost-version']
                    = BoostVersion::from($lib['boost-version']);
        }

        // Preserve the current empty authors tags.
        if (!isset($lib['authors'])) {
            $lib['authors'] = '';
        }

        // Normalize the data representation
        foreach($lib as $key => &$value) {
            if (is_string($value)) {
                $value = trim(preg_replace('@\s+@', ' ', $value));
            }
        }
        if (!empty($lib['category'])) {
            $lib['category'] = array_map('strtolower', $lib['category']);
            sort($lib['category']);
        }

        // Capitilize the names
        if (!empty($lib['name'])) {
            // Not using ucwords because it messes up uBLAS.
            $lib['name'] = preg_replace_callback('@\b[a-z](?![A-Z])@',
                function($matches) { return strtoupper($matches[0]); },
                $lib['name']);
        }

        // Check the status.
        if (isset($lib['status'])) {
            $lib['status'] = strtolower($lib['status']);
            if (!in_array($lib['status'], array('hidden', 'unreleased', 'deprecated', 'removed'))) {
                throw new BoostLibraries_exception("Invalid status: {$lib['status']}");
            }
        }

        $this->details = $lib;
    }

    // This is basically the parent of the 'meta' directory.
    public function set_library_path($library_path) {
        assert(!isset($this->details['library_path']));
        $library_path = trim($library_path, '/').'/';
        $documentation_url =
            isset($this->details['documentation']) ?
            $this->details['documentation'] : '.';
        $this->details['library_path'] = $library_path;
        $this->details['documentation'] =
            ltrim(BoostUrl::resolve($documentation_url, $library_path), '/');
    }

    public function array_for_json($exclude = array()) {
        $details = $this->details;

        $details = self::clean_for_output($details, $exclude);

        foreach ($exclude as $field) {
            if (isset($details[$field])) {
                unset($details[$field]);
            }
        }

        return $details;
    }

    /** Kind of hacky way to fill in details that probably shouldn't be
     *  stored here anyway. */
    public function fill_in_details_from_previous_version($previous = null) {
        if (empty($this->details['boost-version']) &&
            BoostWebsite::array_get($this->details, 'status') != 'removed')
        {
            $this->details['boost-version'] = isset($previous->details['boost-version']) ?
                $previous->details['boost-version'] :
                BoostVersion::unreleased();
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
