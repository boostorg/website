<?php
/*
  Copyright 2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost.php');


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
        //~ print "<!-- boost_fead (0) ".$xml_file." -->\n";
        if (dirname($xml_file) == ".")
        {
            $xml_file = BOOST_RSS_DIR.'/'.$xml_file;
        }
        //~ print "<!-- boost_fead (1) ".$xml_file." -->\n";
        $xml = implode("",file($xml_file));
        //~ print "<!-- boost_fead (2) ".$xml_file." -->\n";
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
                    case 'link':
                    case 'dc:date':
                    {
                        if (isset($val['value']))
                        {
                            $item[strtolower($val['tag'])] = html_entity_decode(trim($val['value']));
                            switch (strtolower($val['tag']))
                            {
                                case 'pubdate':
                                $item['pubdate'] = strtotime($item['pubdate']);
                                $item['date'] = gmdate('F jS, Y H:i ',$item['pubdate']).'GMT';
                                break;
                                
                                case 'dc:date':
                                $item['pubdate'] = strtotime($item['dc:date']);
                                $item['date'] = gmdate('F jS, Y H:i ',$item['pubdate']).'GMT';
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
                $item['guid'] = md5('['.$item['pubdate'].'] '.$item['title']);
                if (!isset($item['link']) || ! $item['link'])
                {
                    $item['link'] = $item_base_uri.'/'.$item['guid'];
                }
                if (isset($item['description']))
                {
                    $desc = preg_split('@<hr( /)?>@i',$item['description']);
                    $item['brief'] = $desc[0];
                    if (isset($desc[1]))
                    {
                        $item['description'] = $desc[1];
                    }
                }
                if (isset($item['title']))
                {
                    preg_match('@^(?:[\[][^\]]+[\]]\s*)*(.*)@i',$item['title'],$title);
                    $item['title'] = $title[1];
                }
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
