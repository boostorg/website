<?php
/*
  Copyright 2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

function _field_cmp_key_($a,$b)
{ return strcmp($a['key'],$b['key']); }

function _field_cmp_title_($a,$b)
{ return strcmp($a['title'],$b['title']); }

function _field_cmp_description_($a,$b)
{ return strcmp($a['description'],$b['description']); }

function _field_cmp_guid_($a,$b)
{ return strcmp($a['guid'],$b['guid']); }

function _field_cmp_pubdate_($a,$b)
{ return _field_cmp_less_($b['pubdate'],$a['pubdate']); }

function _field_cmp_less_($i,$j)
{
    return ($i == $j) ? 0 : (($i < $j) ? -1 : 1);
}

function _field_cmp_($r,$a,$b)
{
    if ($r == 0) { return _field_cmp_pubDate_($a,$b); }
    else { return $r; }
}

class boost_feed
{
    var $db = array();
    
    function boost_feed($xml_file,$item_base_uri)
    {
        $xml = implode("",file($xml_file));
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $xml, $values);
        xml_parser_free($parser);
        
        ##print '<!-- '; print_r($values); print ' -->';
        
        $item = NULL;
        foreach ( $values as $key => $val )
        {
            if ($val['tag'] == 'item' && $val['type'] == 'open')
            {
                $item = array();
            }
            else if ($val['type'] == 'complete')
            {
                switch (strtolower($val['tag']))
                {
                    case 'title':
                    case 'description':
                    case 'guid':
                    case 'pubdate':
                    {
                        if (isset($val['value']))
                        {
                            $item[strtolower($val['tag'])] = html_entity_decode(trim($val['value']));
                            switch (strtolower($val['tag']))
                            {
                                case 'pubdate':
                                $item['pubdate'] = strtotime($item['pubdate']);
                                break;
                                
                                case 'guid':
                                if (preg_match("@[{]([^}]+)[}]@",$item['guid'],$guid))
                                {
                                    $item['guid'] = $guid[1];
                                }
                                $item['link'] = $item_base_uri.'/'.$item['guid'];
                                break;
                            }
                        }
                        else { $item[$val['tag']] = ''; }
                    }
                    break;
                }
            }
            else if ($val['tag'] == 'item' && $val['type'] == 'close' && $item)
            {
                $this->db[$item['guid']] = $item;
                $item = NULL;
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
