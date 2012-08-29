<?php
/*
  Copyright 2007 Redshift Software, Inc.
  Copyright 2012 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

define('BOOST_VERSION_BETA', 0);
define('BOOST_VERSION_RELEASED', 1);

class BoostVersion {
    var $version, $beta;
    static $current;

    function __construct($major, $minor, $point, $beta = false) {
        $this->version = Array($major, $minor, $point);
        $this->beta = $beta;
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
            if (preg_match('@(\d+)[._](\d+)[._](\d+)([._ ]?beta(\d*))?@',
                $value, $matches))
            {
                return new BoostVersion(
                    (int) $matches[1],
                    (int) $matches[2],
                    (int) $matches[3],
                    empty($matches[4]) ? false : (int) $matches[5]
                );
            }
            else
            {
                die("Invalid version");
            }
        }
        else {
            die("Can't convert to BoostVersion.");
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
        static $boost_version;

        if ($boost_version == null) {
            $boost_version = isset($_SERVER["PATH_INFO"]) ?
                BoostVersion::from($_SERVER["PATH_INFO"]) :
                BoostVersion::current();
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
     * Compare this verison with another. Ignores the beta field
     * (i.e. 1.50.0 beta1 == 1.50.0 beta).
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
        return implode('.', $this->version).
            ($this->is_beta() ? ' beta'. $this->beta : '');
    }

    /**
     * The name of the root directory for this version.
     */
    function dir() {
        return 'boost_'.implode('_', $this->version).
            ($this->is_beta() ? '_beta'. $this->beta : '');
    }
}

function boost_set_current_version($major, $minor, $point) {
    global $boost_current_version;
    if (BoostVersion::$current != null)
        die("Setting current version twice.");
    BoostVersion::$current = new BoostVersion($major, $minor, $point);
}
