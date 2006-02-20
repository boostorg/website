<?php
/*
  Copyright 2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

function _field_cmp_key_($a,$b)
{ return strcmp($a['key'],$b['key']); }
function _field_cmp_name_($a,$b)
{ return strcmp($a['name'],$b['name']); }
function _field_cmp_authors_($a,$b)
{ return strcmp($a['authors'],$b['authors']); }
function _field_cmp_description_($a,$b)
{ return strcmp($a['description'],$b['description']); }
function _field_cmp_documentation_($a,$b)
{ return strcmp($a['documentation'],$b['documentation']); }
function _field_cmp_std_proposal_($a,$b)
{ return ($a['std-proposal'] < $b['std-proposal']) ? -1 : 1; }
function _field_cmp_std_tr1_($a,$b)
{ return ($a['std-tr1'] < $b['std-tr1']) ? -1 : 1; }
function _field_cmp_header_only_($a,$b)
{ return ($a['header-only'] < $b['header-only']) ? -1 : 1; }
function _field_cmp_autolink_($a,$b)
{ return ($a['autolink'] < $b['autolink']) ? -1 : 1; }
function _field_cmp_boost_version_($a,$b)
{
    $i = explode('.',$a['boost-version']);
    $j = explode('.',$b['boost-version']);
    if ($i[0] == $j[0] && $i[1] == $j[1]) { return $i[2]-$j[2]; }
    else if ($i[0] == $j[0]) { return $i[1]-$j[1]; }
    else { return $i[0]-$j[0]; }
}

class boost_libraries
{
    var $db = array();
    
    function boost_libraries($xml_file)
    {
        $xml = implode("",file($xml_file));
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $xml, $values);
        xml_parser_free($parser);
        
        ##print '<!-- '; print_r($values); print ' -->';
        
        $lib = NULL;
        foreach ( $values as $key => $val )
        {
            if ($val['tag'] == 'library' && $val['type'] == 'open')
            {
                $lib = array();
            }
            else if ($val['type'] == 'complete')
            {
                switch ($val['tag'])
                {
                    case 'key':
                    case 'boost-version':
                    case 'name':
                    case 'authors':
                    case 'description':
                    case 'documentation':
                    case 'std-proposal':
                    case 'std-tr1':
                    case 'header-only':
                    case 'autolink':
                    {
                        if (isset($val['value'])) { $lib[$val['tag']] = trim($val['value']); }
                        else { $lib[$val['tag']] = ''; }
                    }
                    break;
                }
            }
            else if ($val['tag'] == 'library' && $val['type'] == 'close' && $lib)
            {
                print '<!-- '; print_r($lib); print ' -->';
                $this->db[$lib['key']] = $lib;
                $lib = NULL;
            }
        }
    }
    
    function sort_by($field)
    {
        $f = '_field_cmp_'.strtolower(str_replace('-','_',$field)).'_';
        uasort($this->db,$f);
    }
}
?>
