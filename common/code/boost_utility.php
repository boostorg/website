<?php
/*
  Copyright 2007 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost.php');


function _preg_replace_bounds($front_regex,$back_regex,$front_replace,$back_replace,$text)
{
    $offset = 0;
    $result = '';
    while (TRUE)
    {
        $subject = substr($text,$offset);
        if (preg_match($front_regex,$subject,$begin,PREG_OFFSET_CAPTURE) == 0 ||
            preg_match($back_regex,$subject,$end,PREG_OFFSET_CAPTURE,
                $begin[0][1]+strlen($begin[0][0])) == 0
            )
        { break; }
        else
        {
            $result .= substr($subject,0,$begin[0][1]);
            $result .= preg_replace($front_regex,$front_replace,$begin[0][0]);
            $result .= substr(
                $subject,
                $begin[0][1]+strlen($begin[0][0]),
                $end[0][1]-($begin[0][1]+strlen($begin[0][0])) );
            $result .= preg_replace($back_regex,$back_replace,$end[0][0]);
            $offset += $end[0][1]+strlen($end[0][0]);
        }
    }
    if ($result === '') { return $text; }
    else { return $result . substr($text,$offset); }
}


function _field_cmp_($r,$a,$b)
{
    if ($r == 0) { return _field_cmp_name_($a,$b); }
    else { return $r; }
}

function _field_cmp_authors_($a,$b)
{ return _field_cmp_(strcmp($a['authors'],$b['authors']),$a,$b); }

function _field_cmp_autolink_($a,$b)
{ return _field_cmp_(_field_cmp_less_($a['autolink'],$b['autolink']),$a,$b); }

function _field_cmp_boost_version_($a,$b)
{
    $i = explode('.',$a['boost-version']);
    $j = explode('.',$b['boost-version']);
    if ($i[0] == $j[0] && $i[1] == $j[1]) { return _field_cmp_($i[2]-$j[2],$a,$b); }
    else if ($i[0] == $j[0]) { return _field_cmp_($i[1]-$j[1],$a,$b); }
    else { return _field_cmp_($i[0]-$j[0],$a,$b); }
}

function _field_cmp_description_($a,$b)
{ return strcmp($a['description'],$b['description']); }

function _field_cmp_documentation_($a,$b)
{ return strcmp($a['documentation'],$b['documentation']); }

function _field_cmp_guid_($a,$b)
{ return strcmp($a['guid'],$b['guid']); }

function _field_cmp_header_only_($a,$b)
{ return _field_cmp_(_field_cmp_less_($a['header-only'],$b['header-only']),$a,$b); }

function _field_cmp_key_($a,$b)
{ return strcmp($a['key'],$b['key']); }

function _field_cmp_less_($i,$j)
{
    return ($i == $j) ? 0 : ($i !== FALSE && ($j === FALSE || $i < $j) ? -1 : 1);
}

function _field_cmp_name_($a,$b)
{ return strcasecmp($a['name'],$b['name']); }

function _field_cmp_pubdate_($a,$b)
{ return _field_cmp_less_($b['pubdate'],$a['pubdate']); }

function _field_cmp_std_proposal_($a,$b)
{ return _field_cmp_(_field_cmp_less_($a['std-proposal'],$b['std-proposal']),$a,$b); }

function _field_cmp_std_tr1_($a,$b)
{ return _field_cmp_(_field_cmp_less_($a['std-tr1'],$b['std-tr1']),$a,$b); }

function _field_cmp_title_($a,$b)
{ return strcmp($a['title'],$b['title']); }

?>
