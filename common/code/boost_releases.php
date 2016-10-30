<?php
# Copyright 2016 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

class BoostReleases {
    var $release_file;
    var $release_data = array();

    function __construct($release_file) {
        $this->release_file = $release_file;

        if (is_file($this->release_file)) {
            $release_data = array();
            foreach(BoostState::load($this->release_file) as $version => $data) {
                $data = $this->unflatten_array($data);
                $version_object = BoostVersion::from($version);
                $base_version = $version_object->base_version();
                $version = (string) $version_object;

                if (isset($this->release_data[$base_version][$version])) {
                    echo "Duplicate release data for {$version}.\n";
                }
                $this->release_data[$base_version][$version] = $data;
            }
        }
    }

    function save() {
        $flat_release_data = array();
        foreach($this->release_data as $base_version => $versions) {
            foreach($versions as $version => $data) {
                $flat_release_data[$version] = $this->flatten_array($data);
            }
        }
        BoostState::save($flat_release_data, $this->release_file);
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

    function flatten_array($x, $key_base = '') {
        $flat = array();
        foreach ($x as $sub_key => $value) {
            $key = $key_base ? "{$key_base}.{$sub_key}" : $sub_key;
            if (is_array($value)) {
                $flat = array_merge($flat, $this->flatten_array($value, $key));
            }
            else {
                $flat[$key] = $value;
            }
        }
        return $flat;
    }

    // Expected format:
    //
    // URL
    // (blank line)
    // Output of sha256sum
    function loadReleaseInfo($release_details) {
        if (!preg_match('@
            \A
            \s*([^\s]*)[ \t]*\n
            [ \t]*\n
            (.*)
            @xs', $release_details, $matches))
        {
            throw new BoostException("Error parsing release details");
        }

        $download_page = $matches[1];
        $sha256sums = explode("\n", trim($matches[2]));

        // TODO: Better URL validation?
        if (substr($download_page, -1) != '/') {
            throw new BoostException("Release details needs to start with a directory URL");
        }

        $version = BoostVersion::from($download_page);
        $base_version = $version->base_version();
        $version_string = (string) $version;

        $downloads = array();
        foreach($sha256sums as $sha256sum) {
            if (!preg_match('@^([0-9a-f]{64}) *([a-zA-Z0-9_.]*)$@', trim($sha256sum), $match)) {
                throw new BoostException("Invalid sha256sum: {$sha256sum}");
            }

            $sha256 = $match[1];
            $filename = $match[2];
            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            $extensions = array(
                '7z' => 'windows', 'zip' => 'windows',
                'gz' => 'unix', 'bz2' => 'unix',
            );
            if (!array_key_exists($extension, $extensions)) {
                throw new BoostException("Invalid extension: {$filename}");
            }
            $line_endings = $extensions[$extension];

            $downloads[$extension] = array(
                'line_endings' => $line_endings,
                'url' => "{$download_page}{$filename}",
                'sha256' => $sha256,
            );
        }

        // TODO: Should probably set documentation URL before loading in the
        //       release data, so the array keys should already exist?
        if (!array_key_exists($base_version, $this->release_data)) {
            $this->release_data[$base_version] = array();
        }
        if (!array_key_exists($version_string, $this->release_data[$base_version])) {
            $this->release_data[$base_version][$version_string] = array(
                'release_status' => 'dev',
            );
        }
        $this->release_data[$base_version][$version_string]['download_page'] = $download_page;
        $this->release_data[$base_version][$version_string]['downloads'] = $downloads;
    }

    function addDocumentation($version, $path) {
        $base_version = $version->base_version();
        $version_string = (string) $version;

        if (!array_key_exists($base_version, $this->release_data)) {
            $this->release_data[$base_version] = array();
        }
        if (!array_key_exists($version_string, $this->release_data[$base_version])) {
            $this->release_data[$base_version][$version_string] = array(
                'release_status' => 'dev',
            );
        }
        $this->release_data[$base_version][$version_string]['documentation'] = $path;
    }

    function setReleaseStatus($version, $status) {
        $base_version = $version->base_version();
        $version_string = (string) $version;

        // TODO: Check for more documentation/downloads?
        //       Not sure how strict this should be, releasing without
        //       any information should work okay, but is not desirable
        if (!isset($this->release_data[$base_version][$version_string])) {
            throw new BoostException("No release info for {$version_string}");
        }

        assert(in_array($status, array('released', 'dev')));
        if ($status === 'released') {
            unset($this->release_data[$base_version][$version_string]['release_status']);
            $this->release_data[$base_version][$version_string]['release_date'] = new DateTime();
        }
        else {
            $this->release_data[$base_version][$version_string]['release_status'];
            unset($this->release_data[$base_version][$version_string]['release_date']);
        }
    }
}
