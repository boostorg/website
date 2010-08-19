<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

function text_filter($params)
{
    $params['title'] = htmlentities($params['key']);

    display_template($params['template'],
        new boost_archive_render_callbacks('text_filter_content', $params));
}

function text_filter_content($params)
{
    print "<h3>".htmlentities($params['key'])."</h3>\n";
    print "<pre>\n";
    print_encoded_text($params, 'text');
    print "</pre>\n";
}

// This takes a plain text file and outputs encoded html with marked
// up links.

function print_encoded_text($params, $type) {
    $root = dirname(preg_replace('@([^/]+/)@','../',$params['key']));

    // John Gruber's regular expression for finding urls
    // http://daringfireball.net/2009/11/liberal_regex_for_matching_urls
    
    foreach(preg_split(
        '@\b((?:[\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|[^[:punct:]\s]|/))@',
        $params['content'], -1, PREG_SPLIT_DELIM_CAPTURE)
        as $index => $part)
    {
        if($index % 2 == 0) {
            $html = htmlentities($part);
        
            if($type == 'cpp') {
                $html = preg_replace(
                    '@(#[ ]*include[ ]+&lt;)(boost[^&]+)@Ssm',
                    '${1}<a href="'.$root.'/${2}">${2}</a>',
                    $html );
                $html = preg_replace(
                    '@(#[ ]*include[ ]+&quot;)(boost[^&]+)@Ssm',
                    '${1}<a href="'.$root.'/${2}">${2}</a>',
                    $html );
            }

            print $html;
        }
        else {
            $url = process_absolute_url($part, $root);
            if($url) {
                 print '<a href="'.htmlentities($url).'">'.htmlentities($part).'</a>';
            }
            else {
                print htmlentities($part);
            }
       }
    }
}

function process_absolute_url($url, $root = null) {
    // Simplified version of the 'loose' regular expression from
    // http://blog.stevenlevithan.com/archives/parseuri
    //
    // (c) Steven Levithan <stevenlevithan.com>
    // MIT License

    if(!preg_match(
        '~^'.
        // Protocol(1): (Could also remove the userinfo detection stuff?)
        '(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?'.
        '(?:\/\/)?'.
        // Authority(2)
        '('.
            // User info
            '(?:[^:@]*:?[^:@]*@)?'.
            // Host(3)
            '([^:\/?#]*)'.
            // Port
            '(?::\d*)?'.
        ')'.
        // Relative(4)
        '(\/.*)'.
        '~',
        $url, $matches))
    {
        return;
    }

    $protocol = $matches[1];
    $authority = $matches[2];
    $host = $matches[3];
    $relative = $matches[4];
        
    if(!$authority) return;

    if($root &&
        ($host == 'boost.org' || $host == 'www.boost.org') &&
        (strpos($relative, '/lib') === 0))
    {
        $url = $root.$relative;
    }
    else
    {
        $url = ($protocol ? $protocol : 'http').'://'.$authority.$relative;
    }
    
    return $url;
}
