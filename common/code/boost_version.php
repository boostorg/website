<?php
/*
  Copyright 2007 Redshift Software, Inc.
  Copyright 2012 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/

class BoostVersion {
    // These should be private, but php disagrees.

    /** release_stage is for releases with version information */
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

    /** release_stage for development stages (master, develop etc.) */
    const release_stage_development = 0;

    /** release_stage for anything that isn't linked to a numbered release */
    const release_stage_null = 0;

    /** release_stage for a version before it has entered into the release
        process */
    const release_stage_prerelease = 1;

    /** release_stage for beta releases */
    const release_stage_beta = 2;

    /** release_stage for the final releases */
    const release_stage_final = 3;

    /** The version number */
    private $version = Array(
        'stage' => self::release_stage,
        'major' => 0,
        'minor' => 0,
        'point' => 0,
        'release_stage' => 0,
        'extra' => 0,
    );

    /** The current release version. */
    static $current;

    private function __construct($version) {
        $this->version = array_merge($this->version, $version);
    }

    static function release($major, $minor, $point, $beta = false) {
        return new BoostVersion(Array(
            'major' => $major,
            'minor' => $minor,
            'point' => $point,
            'release_stage' => $beta ?
                self::release_stage_beta : self::release_stage_final,
            'extra' => $beta ?: 0,
        ));
    }

    static function prerelease($major, $minor, $point) {
        return new BoostVersion(Array(
            'major' => $major,
            'minor' => $minor,
            'point' => $point,
            'release_stage' => self::release_stage_prerelease,
        ));
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
     * Returns a BoostVersion representation of the argument.
     * If the argument is a BoostVersion, returns it (not a clone).
     * If it's a valid version string, parse it.
     * Otherwise throws BoostVersion_Exception.
     * @return BoostVersion
     */
    static function from($value) {
        if ($value instanceof BoostVersion) {
            return $value;
        } else if (is_string($value)) {
            $version = self::parseVersion($value);
            if ($version) {
                return $version;
            } else {
                throw new BoostVersion_Exception(
                    "Invalid version: ".html_encode($value));
            }
        } else {
            throw new BoostVersion_Exception("Can't convert to BoostVersion.");
        }
    }

    /**
     * Returns a BoostVersion representation of the version number in the
     * argument, or null if it couldn't be parsed.
     */
    static function parseVersion($version_string) {
        $version_string = strtolower(trim($version_string, " \t\n\r\0\x0B/"));

        switch($version_string) {
            case 'master': return self::master();
            case 'develop': return self::develop();
            case 'latest': return self::latest();
            case 'unreleased': return self::unreleased();
            case 'hidden': return self::hidden();
        }

        if (preg_match('@^(\d+)[._](\d+)[._](\d+)[-._ ]?(?:(b(?:eta)?[- _.]*)(\d*)|(prerelease))?$@', $version_string, $matches))
        {
            return new BoostVersion(Array(
                'major' => (int) $matches[1],
                'minor' => (int) $matches[2],
                'point' => (int) $matches[3],
                'release_stage' =>
                    !empty($matches[4]) ? self::release_stage_beta : (
                    !empty($matches[6]) ? self::release_stage_prerelease :
                    self::release_stage_final),
                'extra' => empty($matches[4]) ? false :
                    (int) ($matches[5] ?: 1),
            ));
        }
        else
        {
            return null;
        }
    }

    /**
     * The current stable release of boost.
     * @return BoostVersion
     */
    static function current() {
        if (BoostVersion::$current == null) {
            BoostVersion::$current = BoostVersion::from(file_get_contents(
                __DIR__.'/../../generated/current_version.txt'));
        }
        return BoostVersion::$current;
    }

    function major() { return $this->version['major']; }
    function minor() { return $this->version['minor']; }
    function point() { return $this->version['point']; }

    /**
     * Is this a beta version?
     * @return boolean
     */
    function is_beta() {
        return $this->version['stage'] === self::release_stage &&
            $this->version['release_stage'] === self::release_stage_beta;
    }

    /**
     * Number of the beta release, or false for not a beta.
     * @return boolean|number
     */
    function beta_number() {
        return $this->is_beta() ? $this->version['extra'] : false;
    }

    /**
     * Is this a numbered release version?
     * Includes prerelease, beta, etc.
     * @return boolean
     */
    function is_numbered_release() {
        return $this->version['stage'] === self::release_stage;
    }

    /**
     * The stage of the release.
     *
     * Not sure about this, this value wasn't mean to be public, but it's
     * needed for updating the documentation list. Maybe would have been better
     * to supply a comparison method?
     * @return int
     */
    function release_stage() {
        return $this->version['release_stage'];

    }

    /**
     * Is this a final release?
     * Not prerelease, beta etc.
     * @return boolean
     */
    function is_final_release() {
        return $this->version['stage'] === self::release_stage &&
            $this->version['release_stage'] === self::release_stage_final;
    }

    /**
     * Is this a valid update version (including develop/master/latest).
     * i.e. update-version can't be unreleased or hidden.
     * @return boolean
     */
    function is_update_version() {
        return $this->version['stage'] <= self::latest_stage;
    }

    /**
     * Is this an unreleased library?
     *
     * Perhaps a bit confused, as it does not include prerelease or beta
     * versions.
     */
    function is_unreleased() {
        return $this->version['stage'] === self::unreleased_stage;
    }

    /**
     * Is this a hidden version? (Used in boost-version for libraries
     * that shouldn't be publically listed. A bit of a hack that I'm
     * somewhat regretting).
     */
    function is_hidden() {
        return $this->version['stage'] === self::hidden_stage;
    }

    /**
     * Compare this version with another.
     * @return int, -1 if less than the other version, 0 if the
     * same, +1 if more
     */
    function compare($x) {
        $x = BoostVersion::from($x);

        return $this->version < $x->version ? -1 :
            ($this->version > $x->version ? 1 : 0);
    }

    /**
     * A string representation appropriate for output.
     */
    function __toString() {
        if ($this->version['stage']) {
            return $this->stage_name();
        }
        else {
            $r = implode('.', $this->version_numbers());
            switch ($this->version['release_stage']) {
            case self::release_stage_beta:
                $r .= ' beta'. $this->version['extra'];
                break;
            case self::release_stage_prerelease:
                $r .= ' prerelease';
                break;
            }
            return $r;
        }
    }

    /**
     * The name of the root directory for this version.
     *
     * Doesn't work for beta versions, as they're not consistent enough.
     * Some examples: boost_1_54_0_beta, boost_1_55_0b1, boost_1_56_0_b1.
     * Also doesn't work for prerelease, as it doesn't have any documentation.
     */
    function dir() {
        return $this->version['stage'] ? $this->stage_name() :
            'boost_'.implode('_', $this->version_numbers()).
            ($this->is_beta() ? '_beta'. $this->version['extra'] : '');
    }

    /**
     * The documentation directory for the final release.
     */
    function final_doc_dir() {
        assert(!$this->version['stage']);
        return implode('_', $this->version_numbers());
    }

    /**
     * The version number without release stage info
     * (i.e. no beta info).
     */
    function base_version() {
        return $this->version['stage'] ? $this->stage_name() :
            implode('.', $this->version_numbers());
    }

    /**
     * The version number without release stage info or point versions
     * (e.g. 1.65 for 1.65 betas and point releases)
     */
    function minor_release() {
        return $this->version['stage'] ? $this->stage_name() :
            implode('.', array_slice($this->version, 1, 2));
    }

    /** Return the git tag/branch for the version.
        Doesn't work for prerelease stage as it doesn't have a tag */
    function git_ref() {
        assert($this->version['release_stage'] != self::release_stage_prerelease);
        return $this->version['stage'] ? $this->stage_name() :
            'boost-'.implode('.', $this->version_numbers()).
            ($this->is_beta() ? '-beta'. $this->version['extra'] : '');
    }

    /** Return the version numbers from the version array */
    private function version_numbers() {
        return array_slice($this->version, 1, 3);
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
            throw new BoostVersion_Exception("Setting current version twice.");
        self::$current = self::release($major, $minor, $point);
    }
}

class BoostVersion_Exception extends BoostException {}
