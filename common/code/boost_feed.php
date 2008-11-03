<?php
/*
  Copyright 2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost.php');

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
                    case 'boostbook:purpose':
                    case 'boostbook:download':
                    {
                        if (isset($val['value']))
                        {
                            $item[strtolower($val['tag'])] = trim($val['value']);
                            switch (strtolower($val['tag']))
                            {
                                case 'pubdate':
                                $item['pubdate'] = strtotime($item['pubdate']);
                                if ($item['pubdate'] != 0)
                                {
                                    $item['date'] = gmdate('F jS, Y H:i ',$item['pubdate']).'GMT';
                                }
                                else
                                {
                                    $item['pubdate'] = time();
                                    $item['date'] = "In Progress";
                                }
                                break;
                                
                                case 'dc:date':
                                $item['pubdate'] = strtotime($item['dc:date']);
                                if ($item['pubdate'] != 0)
                                {
                                    $item['date'] = gmdate('F jS, Y H:i ',$item['pubdate']).'GMT';
                                }
                                else
                                {
                                    $item['pubdate'] = time();
                                    $item['date'] = "In Progress";
                                }
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
                //~ $item['guid'] = md5('['.$item['pubdate'].'] '.$item['title']);
                //~ $item['guid'] = gmdate('Y-m-d-',$item['pubdate'])
                  //~ . preg_replace('@[\W]@i',"_",strtolower($item['title']));
                $item['guid'] = preg_replace('@[\W]@i',"_",strtolower($item['title']));
                if (!isset($item['link']) || ! $item['link'])
                {
                    $item['link'] = $item_base_uri.'/'.$item['guid'];
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
