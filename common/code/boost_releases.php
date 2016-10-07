<?php
# Copyright 2016 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

class BoostReleases {
    var $release_file;
    var $release_data;

    function __construct($release_file) {
        $this->release_file = $release_file;

        if (is_file($this->release_file)) {
            $release_data = array();
            foreach(BoostState::load($this->release_file) as $version => $data) {
                $data = $this->unflatten_array($data);
                $version_object = BoostVersion::from($version);
                $base_version = $version_object->final_doc_dir();
                $version = (string) $version_object;

                if (isset($this->release_data[$base_version][$version])) {
                    echo "Duplicate release data for {$version}.\n";
                }
                $this->release_data[$base_version][$version] = $data;
            }
        }
    }

    function unflatten_array($array) {
        $result = array();
        foreach ($array as $key => $value) {
            $reference = &$result;
            foreach(explode('.', $key) as $key_part) {
                if (!array_key_exists($key_part, $reference)) {
                    $reference[$key_part] = array();
                }
                $reference = &$reference[$key_part];
            }
            $reference = $value;
            unset($reference);
        }
        return $result;
    }
}
