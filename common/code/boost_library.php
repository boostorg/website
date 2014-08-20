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
 *      version = boost version
 *      module  = module name
 *      path    = module path
 */
class BoostLibrary
{
    var $details = null;

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
        assert(isset($lib['key']));

        if (isset($lib['boost-version'])) {
            $lib['boost-version']
                    = BoostVersion::from($lib['boost-version']);
        }

        if (isset($lib['update-version'])) {
            $lib['update-version']
                    = BoostVersion::from($lib['update-version']);
        }
        else if (isset($info['version'])) {
            $lib['update-version'] = BoostVersion::from($info['version']);
        }
        else if (isset($lib['boost-version'])) {
            $lib['update-version'] = $lib['boost-version'];
        }
        else {
            throw new BoostLibraries_exception(
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

        // Normalize the data representation
        foreach($lib as $key => &$value) {
            if (is_string($value)) {
                $value = trim(preg_replace('@\s+@', ' ', $value));
            }
        }
        sort($lib['category']);

        $this->details = $lib;
    }
}
