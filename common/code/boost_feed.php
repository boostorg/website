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
                                case 'dc:date':
                                $old_tz = date_default_timezone_get();
                                date_default_timezone_set('GMT');
                                $item['pubdate'] = strtotime($item[strtolower($val['tag'])]);
                                date_default_timezone_set($old_tz);
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
    
    function echo_download_table($guid)
    {
        if($this->db[$guid]['boostbook:download']) {
            $link = $this->db[$guid]['boostbook:download'];
            if(preg_match('@/boost/(\d+)\.(\d+)\.(\d+)/$@', $link, $matches)) {
                $base_name = 'boost_'.$matches[1].'_'.$matches[2].'_'.$matches[3];
                
                /* Pick which files are available by examining the version number.
                   This could possibly be meta-data in the rss feed instead of being
                   hardcoded here. */
                
                $downloads['unix'][] = $base_name.'.tar.bz2';
                $downloads['unix'][] = $base_name.'.tar.gz';

                if($matches[1] == 1 && $matches[2] >= 32 && $matches[2] <= 33) {
                    $downloads['windows'][] = $base_name.'.exe';
                }
                else if($matches[1] > 1 || $matches[2] > 34 || ($matches[2] == 34 && $matches[3] == 1)) {
                    $downloads['windows'][] = $base_name.'.7z';
                }
                $downloads['windows'][] = $base_name.'.zip';
                
                /* Print the download table. */
                
                echo '<table class="download-table">';
                echo '<caption>Downloads</caption>';
                echo '<tr><th scope="col">Platform</th><th scope="col">File</th></tr>';
                foreach($downloads as $platform => $files) {
                    echo "\n";
                    echo '<tr><th scope="row"';
                    if(count($files) > 1) {
                        echo ' rowspan="'.count($files).'"';
                    }
                    echo '>'.htmlentities($platform).'</th>';
                    foreach($files as $index => $file) {
                        if($index > 0) echo '</tr><tr>';
                        echo '<td><a href="'.htmlentities($link.$file.'/download').'">'.
                            htmlentities($file).'</a></td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';
            }
            else {
                /* If the link didn't match the normal version number pattern
                   then just use the old fashioned link to sourceforge. */

                echo '<p><span class="news-download"><a href="'.
                    htmlentities($link).
                    '">Download this release.</a></span></p>';
            }
        }
    }
}
?>
