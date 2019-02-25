<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
*/

class BoostFilterText extends BoostFilter
{
    function __construct($data, $content) {
        parent::__construct($data, $content);
        $this->charset = 'UTF-8';
    }

    function echo_filtered()
    {
        $this->title = html_encode($this->data->path);

        $this->display_template(
            $this->template_params($this->filter_content()));
    }

    function filter_content()
    {
        return
            "<h3>".html_encode($this->data->path)."</h3>\n".
            "<pre>\n".
            $this->encoded_text('text').
            "</pre>\n";
    }

    // This takes a plain text file and outputs encoded html with marked
    // up links.

    function encoded_text($type) {
        $text = '';

        $root = dirname(preg_replace('@([^/]+/)@','../',$this->data->path))."/";
        $boost_root = "{$root}{$this->data->boost_root}";

        // John Gruber's regular expression for finding urls
        // http://daringfireball.net/2009/11/liberal_regex_for_matching_urls

        foreach(preg_split(
            '@\b((?:[\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|[^[:punct:]\s]|/))@',
            $this->content, -1, PREG_SPLIT_DELIM_CAPTURE)
            as $index => $part)
        {
            if($index % 2 == 0) {
                $html = html_encode($part);

                if($type == 'cpp') {
                    $html = preg_replace(
                        '@(#[ ]*include[ ]+&lt;)(boost[^&]+)@Ssm',
                        '${1}<a href="'.$boost_root.'${2}">${2}</a>',
                        $html );
                    $html = preg_replace(
                        '@(#[ ]*include[ ]+&quot;)(boost[^&]+)@Ssm',
                        '${1}<a href="'.$boost_root.'${2}">${2}</a>',
                        $html );
                }

                $text .= $html;
            }
            else {
                $url = $this->process_absolute_url($part, $root);
                if($url) {
                    $text .= '<a href="'.html_encode($url).'">'.
                        html_encode($part).'</a>';
                }
                else {
                    $text .= html_encode($part);
                }
            }
        }

        return $text;
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
            strpos($relative, '/lib') === 0)
        {
            $url = $root.substr($relative, 1);
        }
        else
        {
            $url = ($protocol ? $protocol : 'http').'://'.$authority.$relative;
        }

        return $url;
    }
}
