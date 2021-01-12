<?php
/*
  Copyright 2007 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/

class BoostUtility
{
    /**
    * Return a callback to comparing the given field.
    * @return callable
    */

    static function sort_by_field($field)
    {
        return array('BoostUtility',
            'cmp_'.strtolower(str_replace('-','_',$field)));
    }

    private static function cmp($r,$a,$b)
    {
        if ($r == 0) { return self::cmp_name($a,$b); }
        else { return $r; }
    }

    static function cmp_authors($a,$b)
    { return self::cmp(strcmp($a['authors'],$b['authors']),$a,$b); }

    static function cmp_boost_version($a,$b)
    {
        return BoostVersion::from($a['boost-version'])
            ->compare($b['boost-version']);
    }

    static function cmp_description($a,$b)
    { return strcmp($a['description'],$b['description']); }

    static function cmp_documentation($a,$b)
    { return strcmp($a['documentation'],$b['documentation']); }

    static function cmp_guid($a,$b)
    { return strcmp($a['guid'],$b['guid']); }

    static function cmp_key($a,$b)
    { return strcmp($a['key'],$b['key']); }

    static function cmp_less($i,$j)
    {
        return ($i == $j) ? 0 : ($i !== FALSE && ($j === FALSE || $i < $j) ? -1 : 1);
    }

    static function cmp_name($a,$b)
    { return strcasecmp($a['name'],$b['name']); }

    static function cmp_pubdate($a,$b)
    { return cmp_less($b['pubdate'],$a['pubdate']); }

    static function cmp_title($a,$b)
    { return strcmp($a['title'],$b['title']); }

    static function cmp_cxxstd($a,$b)
    { return self::cmp(strcmp($a['cxxstd'],$b['cxxstd']),$a,$b); }
}
