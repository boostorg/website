<?php
# Copyright 2016 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)

class BoostReleases {
    var $release_file;
    var $release_data = array();

    function __construct($release_file) {
        $this->release_file = $release_file;

        if (is_file($this->release_file)) {
            $release_data = array();
            foreach(BoostState::load_json($this->release_file) as $key => $data) {
                $data = $this->unflatten_array($data);

                if (preg_match('@^([a-zA-Z][^-]*)-(.*)$@', $key, $match)) {
                    $release_name = $match[1];
                    $version = $match[2];
                }
                else if ($key === '3.1.18') {
                    $release_name = 'bjam';
                    $version = $key;
                }
                else {
                    $release_name = 'boost';
                    $version = $key;
                }
                $version_object = BoostVersion::from($version);

                $key = "{$release_name}-{$version_object->base_version()}";
                $version = (string) $version_object;
                $data['version'] = $version_object;
                $data['release_name'] = $release_name;
                if (array_key_exists('release_date', $data) && is_string($data['release_date'])) {
                    $data['release_date'] = new DateTime($data['release_date']);
                }

                if (isset($this->release_data[$key][$version])) {
                    echo "Duplicate release data for {$release_name} {$version}.\n";
                }
                $this->release_data[$key][$version] = $data;
            }
        }
    }

    function save() {
        $flat_release_data = array();
        foreach($this->release_data as $base_version => $versions) {
            foreach($versions as $version => $data) {
                // Note: Full version number when saving, not base version.
                $key = "{$data['release_name']}-{$version}";
                unset($data['version']);
                unset($data['release_name']);
                $flat_release_data[$key] = $this->flatten_array($data);
            }
        }
        BoostState::save_json($flat_release_data, $this->release_file);
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

    function set_release_data($release_name, $version, $fields) {
        $key = "{$release_name}-{$version->base_version()}";
        $version_string = (string) $version;
        if (!array_key_exists($key, $this->release_data)) {
            $this->release_data[$key] = array();
        }
        if (!array_key_exists($version_string, $this->release_data[$key])) {
            $this->release_data[$key][$version_string] = $this->default_release_data($release_name, $version);
        }
        foreach ($fields as $name => $value) {
            $this->release_data[$key][$version_string][$name] = $value;
        }
    }

    // Get the latest release data for a version
    function get_latest_release_data($release_name, $version) {
        $version = BoostVersion::from($version);
        $key = "{$release_name}-{$version->base_version()}";

        $dev_data = null;
        $dev_version = null;
        $release_data = null;
        $release_version = null;

        // Search for the latest dev and release that matches this page.
        // Q: I don't think there should ever be more than one dev
        //    version, but if there is, does it make any sense to use
        //    the last one?
        foreach (BoostWebsite::array_get($this->release_data, $key, array()) as $version2 => $data) {
            $version_object = BoostVersion::from($version2);

            if (array_key_exists('release_status', $data) && $data['release_status'] == 'dev') {
                if (!$dev_version || $version_object->compare($dev_version) > 0) {
                    $dev_version = $version_object;
                    $dev_data = $data;
                }
            }
            else {
                if (!$release_version || $version_object->compare($release_version) > 0) {
                    $release_version = $version_object;
                    $release_data = $data;
                }
            }
        }

        // If there is going to be another release of this base version, then
        // create dev data if we don't already have any.
        // TODO: prerelease is wrong after a beta. Maybe just use the base
        // version?
        if (!$dev_data && (!$release_version || !$release_version->is_final_release())) {
            $dev_data = $this->default_release_data($release_name,
                    BoostVersion::from("{$version->base_version()} prerelease"));
        }

        $result = array();
        if ($dev_data) { $result['dev'] = $dev_data; }
        if ($release_data) { $result['release'] = $release_data; }
        return $result;
    }

    function default_release_data($release_name, $version) {
        if ($release_name == 'boost' && $version->compare('1.61.0') < 0) {
            // Assume old versions are released if there's no data.
            return array(
                'release_name' => 'boost',
                'version' => $version,
            );
        }
        else {
            // For newer versions, release info hasn't been added yet
            // so default to dev version.
            return array(
                'release_name' => $release_name,
                'version' => $version,
                'release_status' => 'dev',
                'documentation' => '/doc/libs/master/',
            );
        }
    }

    // Expected format:
    //
    // URL
    // (blank line)
    // Output of sha256sum
    function loadReleaseInfo($release_details, $release_name, $release_version = null) {
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

        if (!is_null($release_version)) {
            $version = BoostVersion::from($release_version);
        } else {
            if (!preg_match('@/(?:boost|boostorg/beta|boostorg/release)/([0-9][^/]*)/@', $download_page, $match)) {
                throw new BoostException("Error extracting boost version from download page URL");
            }

            $version = BoostVersion::from($match[1]);
        }

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

        $data = $this->set_release_data($release_name, $version, array(
            'download_page' => $download_page,
            'downloads' => $downloads
        ));
    }

    function addDocumentation($release_name, $version, $path) {
        $data = $this->set_release_data($release_name, $version, array(
            'documentation' => $path,
        ));
    }

    function setReleaseStatus($release_name, $version, $status) {
        $key = "{$release_name}-{$version->base_version()}";
        $version_string = (string) $version;

        // TODO: Check for more documentation/downloads?
        //       Not sure how strict this should be, releasing without
        //       any information should work okay, but is not desirable
        if (!isset($this->release_data[$key][$version_string])) {
            throw new BoostException("No release info for {$version_string}");
        }

        assert(in_array($status, array('released', 'dev')));
        if ($status === 'released') {
            unset($this->release_data[$key][$version_string]['release_status']);
            if (empty($this->release_data[$key][$version_string]['release_date'])) {
                $this->release_data[$key][$version_string]['release_date'] = new DateTime();
            }
        }
        else {
            $this->release_data[$key][$version_string]['release_status'];
            unset($this->release_data[$key][$version_string]['release_date']);
        }
    }
}
