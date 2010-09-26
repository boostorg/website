<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

require_once(dirname(__FILE__).'/boost_filters.php');

function boost_libs_filter($params)
{
    html_init($params);
    $text = extract_html_body($params['content']);
    if($text) {
        $text = prepare_html($text, true);
        $text = remove_html_banner($text);
        $text = prepare_themed_html($text);
        
        display_template($params['template'],
            boost_archive_render_callbacks($text, $params));
    }
    else {
        print $params['content'];
    }
}

function prepare_themed_html($text) {
    $text = preg_replace(
        '@(<a[^>]+>[\s]*)?<img.*boost\.png[^>]*>([\s]*</a>)?@i',
        '',
        $text );
    $text = preg_replace(
        '@<img(.*)align="?right"?[^>]*>@i',
        '<img${1} class="right-inset" />',
        $text );
    $text = preg_replace(
        '@<img(.*)align="?absmiddle"?[^>]*>@i',
        '<img${1} class="inline" />',
        $text );
    /* Remove certain attributes */
    $text = preg_replace(
        '@[\s]+(border|cellpadding|cellspacing|width|height|valign|align|frame|rules|naturalsizeflag|background)=("[^"]*"?|\'[^\']*\'?|[^\s/>]+)@i',
        '',
        $text );
    $text = preg_replace(
        '@<table[\s]+(border)[^\s>]*@i',
        '<table',
        $text );
    $text = preg_replace(
        '@<[/]?(font|hr)[^>]*>@i',
        '',
        $text );
    $text = preg_replace(
        '@<([^\s]+)[\s]+>@i',
        '<${1}>',
        $text );
    $text = _preg_replace_bounds(
        '@<blockquote>[\s]*(<pre>)@i','@(</pre>)[\s]*</blockquote>@i',
        '${1}','${1}',
        $text );
    $text = _preg_replace_bounds(
        '@<blockquote>[\s]*(<p>)@i','@(</p>)[\s]*</blockquote>@i',
        '${1}','${1}',
        $text );
    $text = _preg_replace_bounds(
        '@<blockquote>[\s]*(<table>)@i','@(</table>)[\s]*</blockquote>@i',
        '${1}','${1}',
        $text );
    $text = _preg_replace_bounds(
        '@<blockquote>[\s]*<li>@i','@</li>[\s]*</blockquote>@i',
        '<ul><li>','</li></ul>',
        $text );
    $text = _preg_replace_bounds(
        '@(?:<blockquote>[\s]*)+<h2>@i','@</h2>(?:[\s]*</blockquote>)+@i',
        '<h2>','</h2>',
        $text );
    $text = preg_replace(
        '@(<a name=[^\s>]+[\s]*>)[\s]*(</?[^a])@i',
        '${1}</a>${2}',
        $text );
    $text = preg_replace(
        '@<table>([\s]+<tr>[\s]+<td>.*_arr.*</td>[\s]+<td>.*</td>[\s]+<td>.*</td>[\s]+</tr>[\s]+)</table>@i',
        '<table class="pyste-nav">${1}</table>',
        $text );
    $text = preg_replace(
        '@<table>([\s]+<tr>[\s]+<td)[\s]+class="note_box">@i',
        '<table class="note_box">${1}>',
        $text );
    $text = preg_replace(
        '@<table>([\s]+<tr>[\s]+<td[\s]+class="table_title">)@i',
        '<table class="toc">${1}',
        $text );
    $text = preg_replace(
        '@src=".*theme/u_arr\.gif"@i',
        'src="/gfx/space.png" class="up_image"',
        $text );
    $text = preg_replace(
        '@src=".*theme/l_arr\.gif"@i',
        'src="/gfx/space.png" class="prev_image"',
        $text );
    $text = preg_replace(
        '@src=".*theme/r_arr\.gif"@i',
        'src="/gfx/space.png" class="next_image"',
        $text );
    $text = preg_replace(
        '@src=".*theme/u_arr_disabled\.gif"@i',
        'src="/gfx/space.png" class="up_image_disabled"',
        $text );
    $text = preg_replace(
        '@src=".*theme/l_arr_disabled\.gif"@i',
        'src="/gfx/space.png" class="prev_image_disabled"',
        $text );
    $text = preg_replace(
        '@src=".*theme/r_arr_disabled\.gif"@i',
        'src="/gfx/space.png" class="next_image_disabled"',
        $text );
    $text = preg_replace(
        '@src=".*theme/note\.gif"@i',
        'src="/gfx/space.png" class="note_image"',
        $text );
    $text = preg_replace(
        '@src=".*theme/alert\.gif"@i',
        'src="/gfx/space.png" class="caution_image"',
        $text );
    $text = preg_replace(
        '@src=".*theme/bulb\.gif"@i',
        'src="/gfx/space.png" class="tip_image"',
        $text );
    $text = preg_replace(
        '@<img src=".*theme/(?:bullet|lens)\.gif">@i',
        '',
        $text );
    $text = preg_replace(
        '@(<img src=".*theme/(?:arrow)\.gif")>@i',
        '${1} class="inline">',
        $text );
    return $text;
}
