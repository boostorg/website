<?php
/*
  Copyright 2007 Redshift Software, Inc.
  Copyright 2012 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

class BoostVersion {
    // These should be private, but php disagrees.

    /** release_stage is for full releases, which should have version
     *  information */
    const release_stage = 0;

    /** The contents of the master branch of the super project */
    const master_stage = 1;

    /** The contents of the develop branch of the super project */
    const develop_stage = 2;

    /** The contents of the latest branch of the super project */
    const latest_stage = 3;

    /** Unreleased libraries (should only be used in 'boost-version' field) */
    const unreleased_stage = 4;

    /** Hidden libraries (should only be used in 'boost-version' field) */
    const hidden_stage = 5;

    /** The version number */
    private $version = Array(
        'stage' => self::release_stage,
        'major' => 0,
        'minor' => 0,
        'point' => 0
    );

    /** True for beta releases. This is a bit broken */
    private $beta = false;

    /** The current release version. */
    static $current;

    private function __construct($version) {
        $this->version = array_merge($this->version, $version);
    }

    static function release($major, $minor, $point, $beta = false) {
        $version = new BoostVersion(Array(
            'major' => $major,
            'minor' => $minor,
            'point' => $point
        ));
        $version->beta = $beta;
        return $version;
    }

    static function master() {
        return new BoostVersion(Array('stage' => self::master_stage));
    }

    static function develop() {
        return new BoostVersion(Array('stage' => self::develop_stage));
    }

    static function latest() {
        return new BoostVersion(Array('stage' => self::latest_stage));
    }

    static function unreleased() {
        return new BoostVersion(Array('stage' => self::unreleased_stage));
    }

    static function hidden() {
        return new BoostVersion(Array('stage' => self::hidden_stage));
    }

    /**
     * Return a BoostVersion representation of value.
     * @return BoostVersion
     */
    static function from($value) {
        if ($value instanceof BoostVersion) {
            return $value;
        }
        else if (is_string($value)) {
            $value = trim($value, " \t\n\r\0\x0B/");

            switch($value) {
                case 'master': return self::master();
                case 'develop': return self::develop();
                case 'latest': return self::latest();
                case 'unreleased': return self::unreleased();
                case 'hidden': return self::hidden();
            }

            // TODO: Make this stricter by only matching whole string. Might break something?
            if (preg_match('@(?<!\d)(\d+)[._](\d+)[._](\d+)([-._ ]?b(?:eta)?(\d*))?@',
                $value, $matches))
            {
                return self::release(
                    (int) $matches[1],
                    (int) $matches[2],
                    (int) $matches[3],
                    empty($matches[4]) ? false : (int) ($matches[5] ?: 1)
                );
            }
            else
            {
                throw new BoostVersion_Exception(
                    "Invalid version: ".html_encode($value));
            }
        }
        else {
            die("Can't convert to BoostVersion.\n");
        }
    }

    /**
     * The current stable release of boost.
     * @return BoostVersion
     */
    static function current() {
        if (BoostVersion::$current == null)
            die("Version not set.");
        return BoostVersion::$current;
    }

    /**
     * The version the current page is displaying.
     * @return BoostVersion
     */
    static function page() {
        return self::page_version() ?: self::current();
    }

    static function page_title() {
        $version = self::page_version();
        return $version ? "Boost {$version}" : "Boost";
    }

    static private function page_version() {
        static $boost_version;

        if ($boost_version == null) {
            $boost_version = isset($_SERVER["PATH_INFO"]) ?
                BoostVersion::from(strtok($_SERVER["PATH_INFO"], '/')) :
                false;
        }

        return $boost_version;
    }

    /**
     * Is this a beta version?
     * @return boolean
     */
    function is_beta() {
        return $this->beta !== false;
    }

    /**
     * Is this a numbered release version?
     * (as opposed to a develop branch)
     * @return boolean
     */
    function is_numbered_release() {
        return $this->version['stage'] === self::release_stage;
    }

    /**
     * Is this a release version (including develop/master/latest).
     */
    function is_release() {
        return $this->version['stage'] <= self::latest_stage;
    }

    function is_unreleased() {
        return $this->version['stage'] === self::unreleased_stage;
    }

    /**
     * Compare this verison with another. Ignores the beta field
     * (i.e. 1.50.0 beta1 == 1.50.0 beta).
     * @return int, -1 if less than the other version, 0 if the
     * same, +1 if more
     */
    function compare($x) {
        $x = BoostVersion::from($x);

        // Full releases come after betas.
        $beta = ($this->beta === false) ? 9999 : $this->beta;
        $x_beta = ($x->beta === false) ? 9999 : $x->beta;

        return $this->version < $x->version ? -1 :
            ($this->version > $x->version ? 1 : 0) ?:
            ($beta < $x_beta ? -1 : ($beta > $x_beta ? 1 : 0));
    }

    /**
     * A string representation appropriate for output.
     */
    function __toString() {
        return $this->version['stage'] ? $this->stage_name() :
            implode('.', $this->version_numbers()).
            ($this->is_beta() ? ' beta'. $this->beta : '');
    }

    /**
     * The name of the root directory for this version.
     *
     * Doesn't work for beta versions, as they're not consistent enough.
     * Some examples: boost_1_54_0_beta, boost_1_55_0b1, boost_1_56_0_b1.
     */
    function dir() {
        return $this->version['stage'] ? $this->stage_name() :
            'boost_'.implode('_', $this->version_numbers()).
            ($this->is_beta() ? '_beta'. $this->beta : '');
    }

    /** Return the git tag/branch for the version */
    function git_ref() {
        return $this->version['stage'] ? $this->stage_name() :
            'boost-'.implode('.', $this->version_numbers()).
            ($this->is_beta() ? '-beta'. $this->beta : '');
    }

    /** Return the version numbers from the version array */
    private function version_numbers() {
        $numbers = $this->version;
        array_shift($numbers);
        return $numbers;
    }

    /** Return the name of an unversioned stage */
    private function stage_name() {
        switch($this->version['stage']) {
            case self::master_stage: return 'master';
            case self::develop_stage: return 'develop';
            case self::latest_stage: return 'latest';
            case self::unreleased_stage: return 'unreleased';
            case self::hidden_stage: return 'hidden';
            default: assert(false);
        }
    }

    static function set_current($major, $minor, $point) {
        if (self::$current != null)
            die("Setting current version twice.");
        self::$current = self::release($major, $minor, $point);
    }
}

class BoostVersion_Exception extends RuntimeException {}
