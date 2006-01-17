<?php
/*
  Copyright 2005 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

if ($_SERVER['HTTP_HOST'] === 'boost.sourceforge.net') {
}
else if ($_SERVER['HTTP_HOST'] === 'boost.borg.redshift-software.com:8080') {
    @define('ARCHIVE_PREFIX', 'C:/DevRoots/Boost/boost_');
    @define('UNZIP', 'unzip');
}
else if ($_SERVER['HTTP_HOST'] === 'boost.redshift-software.com') {
    @define('ARCHIVE_PREFIX', '/export/website/boost/archives/boost_');
    @define('UNZIP', '/usr/local/bin/unzip');
}
@define('ARCHIVE_FILE_PREFIX', 'boost_');

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

class archive_file
{
    var $key_ = NULL;
    var $file_ = NULL;
    var $archive_ = NULL;
    var $extractor_ = NULL;
    var $type_ = NULL;
    
    function archive_file($pattern, $vpath)
    {
        $path_parts = array();
        preg_match($pattern, $vpath, $path_parts);
        
        /*static*/ $info_map = NULL;
        if ($info_map == NULL)
        {
            $info_map = array(
                array('/^libs\/iostreams\/doc\/.*(html|htm)$/i','raw','text/html'),
                array('/^libs\/preprocessor\/doc\/.*(html|htm)$/i','raw','text/html'),
                array('/^libs\/serialization\/doc\/.*(html|htm)$/i','raw','text/html'),
                array('/^libs\/.*(html|htm)$/i','boost_libs_html','text/html'),
                array('/^doc\/html\/.*html$/i','boost_book_html','text/html'),
                array('/[.](txt|py|rst)$/i','text','text/plain'),
                array('/[.](cpp|hpp)$/i','cpp','text/plain'),
                array('/[.]png$/i','raw','image/png'),
                array('/[.]gif$/i','raw','image/gif'),
                array('/[.](jpg|jpeg|jpe)$/i','raw','image/jpeg'),
                array('/[.]css$/i','raw','text/css'),
                array('/[.]js$/i','raw','application/x-javascript'),
                array('/[.]pdf$/i','raw','application/pdf'),
                array('/^boost\/.*$/i','text','text/plain'),
                );
        }
        
        $this->key_ = $path_parts[2];
        $this->file_ = ARCHIVE_FILE_PREFIX . $path_parts[1] . '/' . $path_parts[2];
        $this->archive_ = str_replace('\\','/', ARCHIVE_PREFIX . $path_parts[1] . '.zip');
        
        foreach ($info_map as $i)
        {
            if (preg_match($i[0],$this->key_))
            {
                $this->extractor_ = $i[1];
                $this->type_ = $i[2];
                break;
            }
        }
    }
    
    function is_raw()
    {
        return $this->extractor_ == 'raw';
    }

    function extract()
    {
        if ($this->extractor_)
        {
            $f = '_extract_' . $this->extractor_;
            $unzip = UNZIP . ' -p "' . $this->archive_ . '" "' . $this->file_ . '"';
            $this->$f($unzip);
        }
        else
        {
            # File doesn't exist, or we don't know how to handle it.
            header("HTTP/1.0 404 Not Found");
        }
    }

    function _extract_string($unzip)
    {
        $file_handle = popen($unzip,'rb');
        $text = '';
        while ($file_handle && !feof($file_handle)) {
            $text .= fread($file_handle,8*1024);
        }
        pclose($file_handle);
        return $text;
    }

    function _extract_raw($unzip)
    {
        header('Content-type: '.$this->type_);
        ## header('Content-Disposition: attachment; filename="downloaded.pdf"');
        $file_handle = popen($unzip,'rb');
        fpassthru($file_handle);
        pclose($file_handle);
    }

    function _extract_text($unzip)
    {
        print "<h3>".$this->key_."</h3>\n";
        print "<pre>\n";
        print htmlentities($this->_extract_string($unzip));
        print "</pre>\n";
    }

    function _extract_cpp($unzip)
    {
        print "<h3>".$this->key_."</h3>\n";
        print "<pre>\n";
        $root = dirname(preg_replace('@([^/]+/)@','../',$this->key_));
        $text = htmlentities($this->_extract_string($unzip));
        $text = preg_replace(
            '@(#[ ]*include[ ]*&lt;)(boost[^&]+)@Ssm',
            '${1}<a href="'.$root.'/${2}">${2}</a>',
            $text );
        print $text;
        print "</pre>\n";
    }

    function _extract_boost_book_html($unzip)
    {
        $text = $this->_extract_string($unzip);
        $text = substr($text,strpos($text,'<div class="spirit-nav">'));
        $text = substr($text,0,strpos($text,'</body>'));
        for ($i = 0; $i < 8; $i++) {
            $text = preg_replace(
                '@<img src="[\./]*images/(.*\.png)" alt="(.*)"([ ][/])?>@Ssm',
                '<img src="/style/css_0/${1}" alt="${2}" />',
                $text );
        }
        $text = str_replace('<hr>','',$text);
        $text = str_replace('<table width="100%">','<table class="footer-table">',$text);
        $text = preg_replace(
            '@[\s]+(border|cellpadding|cellspacing|width|height|valign|frame|rules|naturalsizeflag|background)=[^\s>]+@i',
            '',
            $text );
        
        print $text;
    }

    function _extract_boost_libs_html($unzip)
    {
        $text = $this->_extract_string($unzip);
        
        preg_match('@<title>([^<]+)</title>@i',$text,$title);
        
        preg_match('@<body[^>]*>@i',$text,$body_begin,PREG_OFFSET_CAPTURE);
        preg_match('@</body>@i',$text,$body_end,PREG_OFFSET_CAPTURE);
        $text = substr($text,
            $body_begin[0][1]+strlen($body_begin[0][0]),
            $body_end[0][1]-($body_begin[0][1]+strlen($body_begin[0][0])) );
        
        # nasty code, because (?!fubar) causes an ICE...
        preg_match('@<table@i',$text,$table_begin,PREG_OFFSET_CAPTURE);
        preg_match('@</table>@i',$text,$table_end,PREG_OFFSET_CAPTURE);
        if (isset($table_end[0]) && strpos(substr($text,0,$table_end[0][1]),
            'boost.png',$table_begin[0][1]) !== FALSE)
        {
            $text = substr($text,$table_end[0][1]+strlen($table_end[0][0]));
        }
        
        preg_match('@<h[12]@i',$text,$h1_begin,PREG_OFFSET_CAPTURE);
        preg_match('@</h[12]>@i',$text,$h1_end,PREG_OFFSET_CAPTURE);
        preg_match('@<table@i',$text,$table_begin,PREG_OFFSET_CAPTURE);
        preg_match('@</table>@i',$text,$table_end,PREG_OFFSET_CAPTURE);
        if (isset($h1_begin[0]) && isset($table_end[0]) &&
            $table_begin[0][1] < $h1_begin[0][1] && $h1_begin[0][1] < $table_end[0][1]+8)
        {
            $text = substr($text,$h1_begin[0][1],
                $h1_begin[0][1]-$h1_end[0][1]+5) . substr($text,$table_end[0][1]+8);
        }
        #else
        #{
        #    $text = substr($text,$h1_begin[0][1]);
        #}
        $text = "<h1>${title[1]}</h1>\n" . $text;
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
        $text = preg_replace(
            '@[\s]+(border|cellpadding|cellspacing|width|height|valign|align|frame|rules|naturalsizeflag|background)=[^\s>]+@i',
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
            '@src=".*theme/u_arr\.gif"@i',
            'src="/style/css_0/up.png"',
            $text );
        $text = preg_replace(
            '@src=".*theme/l_arr\.gif"@i',
            'src="/style/css_0/prev.png"',
            $text );
        $text = preg_replace(
            '@src=".*theme/r_arr\.gif"@i',
            'src="/style/css_0/next.png"',
            $text );
        $text = preg_replace(
            '@src=".*theme/u_arr_disabled\.gif"@i',
            'src="/style/css_0/up_disabled.png"',
            $text );
        $text = preg_replace(
            '@src=".*theme/l_arr_disabled\.gif"@i',
            'src="/style/css_0/prev_disabled.png"',
            $text );
        $text = preg_replace(
            '@src=".*theme/r_arr_disabled\.gif"@i',
            'src="/style/css_0/next_disabled.png"',
            $text );
        $text = preg_replace(
            '@src=".*theme/note\.gif"@i',
            'src="/style/css_0/note.png"',
            $text );
        $text = preg_replace(
            '@src=".*theme/alert\.gif"@i',
            'src="/style/css_0/caution.png"',
            $text );
        $text = preg_replace(
            '@src=".*theme/bulb\.gif"@i',
            'src="/style/css_0/tip.png"',
            $text );
        $text = preg_replace(
            '@<img src=".*theme/(?:bullet|lens)\.gif">@i',
            '',
            $text );
        $text = preg_replace(
            '@(<img src=".*theme/(?:arrow)\.gif")>@i',
            '${1} class="inline">',
            $text );
        
        print $text;
    }
}
?>
