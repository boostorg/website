<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

/*
 * HTML processing functions
 */

function html_init($params)
{
    preg_match('@text/html; charset=([^\s"\']+)@i',$params['content'],$charset);
    if (isset($charset[1]))
    {
        $params['charset'] = $charset[1];
    }
    
    preg_match('@<title>([^<]+)</title>@i',$params['content'],$title);
    if (isset($title[1]))
    {
        $params['title'] = $title[1];
    }
}

function extract_html_body($text) {
    preg_match('@<body[^>]*>@i',$text,$body_begin,PREG_OFFSET_CAPTURE);
    preg_match('@</body>@i',$text,$body_end,PREG_OFFSET_CAPTURE);
    if (!isset($body_begin[0]))
    {
        //~ Attempt to recover some content from illegal HTML that is missing the body tag.
        preg_match('@</head>@i',$text,$body_begin,PREG_OFFSET_CAPTURE);
    }
    if (!isset($body_begin[0]))
    {
        //~ Attempt to recover some content from illegal HTML that is missing the body tag.
        preg_match('@<html[^>]*>@i',$text,$body_begin,PREG_OFFSET_CAPTURE);
    }
    if (!isset($body_begin[0]))
    {
        //~ Attempt to recover some content from illegal HTML that is missing the body tag.
        preg_match('@<(hr|div|img|p|h1|h2|h3|h4)[^>]*>@i',$text,$body_begin,PREG_OFFSET_CAPTURE);
    }
    if (!isset($body_begin[0]))
    {
        return;
    }
    else if (!isset($body_end[0]))
    {
        $text = substr($text,
            $body_begin[0][1]+strlen($body_begin[0][0]));
    }
    else
    {
        $text = substr($text,
            $body_begin[0][1]+strlen($body_begin[0][0]),
            $body_end[0][1]-($body_begin[0][1]+strlen($body_begin[0][0])) );
    }

    return $text;
}

function prepare_html($text) {
    $text = preg_replace(
        '@href="?http://www.boost.org/?([^"\s]*)"?@i',
        'href="/${1}"',
        $text );
    $text = preg_replace(
        '@href="?http://boost.org/?([^"\s]*)"?@i',
        'href="/${1}"',
        $text );
    $text = preg_replace(
        '@href="?(?:\.\./)+people/(.*\.htm)"?@i',
        'href="/users/people/${1}l"',
        $text );
    $text = preg_replace(
        '@href="?(?:\.\./)+(LICENSE_[^"\s]*\.txt)"?@i',
        'href="/${1}"',
        $text );
    $text = preg_replace(
        '@<a\s+(class="[^"]+")?\s*href="?(http|mailto)(:[^"\s]*)"?@i',
        '<a class="external" href="${2}${3}"',
        $text );
    
    return $text;
}

function remove_html_banner($text) {

    # nasty code, because (?!fubar) causes an ICE...
    preg_match('@<table[^<>]*>?@i',$text,$table_begin,PREG_OFFSET_CAPTURE);
    preg_match('@</table>@i',$text,$table_end,PREG_OFFSET_CAPTURE);
    if (isset($table_begin[0]) && isset($table_end[0])) {
        $table_contents_start = $table_begin[0][1] + strlen($table_begin[0][0]);
        $table_contents = substr($text, $table_contents_start,
            $table_end[0][1] - $table_contents_start);

        if(strpos($table_contents, 'boost.png') !== FALSE) {
            preg_match('@<td[^<>]*>?([^<]*<(h[12]|p).*?)</td>@is', $table_contents,
                $table_contents_header, PREG_OFFSET_CAPTURE);
            
            $head = substr($text, 0, $table_begin[0][1]);
            $header = isset($table_contents_header[1]) ? $table_contents_header[1][0] : '';
            $tail = substr($text, $table_end[0][1] + strlen($table_end[0][0]));
            $tail = preg_replace('@^\s*<hr\s*/?>\s*@', '', $tail);
                
            $text = $head.$header.$tail;
            return $text;
        }
    }

    $parts = preg_split('@(?=<(p|blockquote))@', $text, 2);
    $header = $parts[0];
    $content = $parts[1];
    
    $header = preg_replace('@(<h\d>\s*)<img[^>]*src="(\.\.\/)*boost\.png"[^>]*>@', '$1', $header);    
    $header = preg_replace('@<img[^>]*src="(\.\.\/)*boost\.png"[^>]*>\s*<[hb]r.*?>@', '', $header);

    return $header.$content;
}

