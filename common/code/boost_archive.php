<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost.php');

class boost_archive
{
    var $version_ = NULL;
    var $key_ = NULL;
    var $file_ = NULL;
    var $archive_ = NULL;
    var $extractor_ = NULL;
    var $type_ = NULL;
    var $head_content_ = NULL;
    var $content_ = NULL;
    
    function boost_archive(
        $pattern,
        $vpath,
        $content_map = array(),
        $get_as_raw = false,
        $archive_subdir = true,
        $archive_dir = ARCHIVE_DIR,
        $archive_file_prefix = ARCHIVE_FILE_PREFIX)
    {
        $path_parts = array();
        preg_match($pattern, $vpath, $path_parts);
        
        $info_map = array_merge($content_map, array(
            array('@.*@','@[.](txt|py|rst|jam|v2|bat|sh|xml)$@i','text','text/plain'),
            array('@.*@','@[.](c|h|cpp|hpp)$@i','cpp','text/plain'),
            array('@.*@','@[.]png$@i','raw','image/png'),
            array('@.*@','@[.]gif$@i','raw','image/gif'),
            array('@.*@','@[.](jpg|jpeg|jpe)$@i','raw','image/jpeg'),
            array('@.*@','@[.]css$@i','raw','text/css'),
            array('@.*@','@[.]js$@i','raw','application/x-javascript'),
            array('@.*@','@[.]pdf$@i','raw','application/pdf'),
            array('@.*@','@[.](html|htm)$@i','raw','text/html'),
            array('@.*@','@[^.](Jamroot|Jamfile)$@i','text','text/plain'),
            ));
        
        $this->version_ = $path_parts[1];
        $this->key_ = $path_parts[2];
        if ($archive_subdir)
        {
            $this->file_ = $archive_file_prefix . $this->version_ . '/' . $this->key_;
        }
        else
        {
            $this->file_ = $archive_file_prefix . $this->key_;
        }
        $this->archive_ = str_replace('\\','/', $archive_dir . '/' . $this->version_ . '.zip');

        foreach ($info_map as $i)
        {
            if (preg_match($i[1],$this->key_))
            {
                $this->extractor_ = $i[2];
                $this->type_ = $i[3];
                break;
            }
        }
        
        $unzip =
          UNZIP
          .' -p '.escapeshellarg($this->archive_)
          .' '.escapeshellarg($this->file_);
        if (! $this->extractor_)
        {
            # File doesn't exist, or we don't know how to handle it.
            $this->extractor_ = '404';
            $this->_init_404();
        }
        else if ($get_as_raw || $this->extractor_ == 'raw')
        {
            $this->_extract_raw($unzip);
            //~ print "--- $unzip";
        }
        else
        {
            /* We pre-extract so we can get this like meta tag information
               before we have to print it out. */
            $this->content_ = $this->_extract_string($unzip);
            $f = '_init_'.$this->extractor_;
            $this->$f();
            if ($this->extractor_ == 'simple')
            {
                $f = '_content_'.$this->extractor_;
                $this->$f();
            }
        }
    }
    
    function content_head()
    {
        if ($this->head_content_)
        {
            print $this->head_content_;
        }
        else
        {
            print <<<HTML
  <title>Boost C++ Libraries</title>
  <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
HTML
                ;
        }
    }
    
    function is_raw()
    {
        return $this->extractor_ == 'raw' || $this->extractor_ == 'simple';
    }

    function _extract_string($unzip)
    {
        $file_handle = popen($unzip,'r');
        $text = '';
        while ($file_handle && !feof($file_handle)) {
            $text .= fread($file_handle,8*1024);
        }
        if(pclose($file_handle) == 0) {
            return $text;
        }
        else {
            $this->extractor_ = '404';
            return '';
        }
    }

    function _extract_raw($unzip)
    {
        header('Content-type: '.$this->type_);
        ## header('Content-Disposition: attachment; filename="downloaded.pdf"');
        $file_handle = popen($unzip,'rb');
        fpassthru($file_handle);
        $exit_status = pclose($file_handle);
        
        // Disable the error message because it was appearing for several files.
        //
        // if($exit_status != 0)
        //     echo 'Error extracting file: '.unzip_error($exit_status);
    }
    
    function content()
    {
        if ($this->extractor_)
        {
            $f = '_content_'.$this->extractor_;
            $this->$f();
        }
    }

    function _init_text()
    {
    }
    
    function _content_text()
    {
        print "<h3>".$this->key_."</h3>\n";
        print "<pre>\n";
        print htmlentities($this->content_);
        print "</pre>\n";
    }

    function _init_cpp()
    {
    }

    function _content_cpp()
    {
        $text = htmlentities($this->content_);
        
        print "<h3>".$this->key_."</h3>\n";
        print "<pre>\n";
        $root = dirname(preg_replace('@([^/]+/)@','../',$this->key_));
        $text = preg_replace(
            '@(#[ ]*include[ ]+&lt;)(boost[^&]+)@Ssm',
            '${1}<a href="'.$root.'/${2}">${2}</a>',
            $text );
        $text = preg_replace(
            '@(#[ ]*include[ ]+&quot;)(boost[^&]+)@Ssm',
            '${1}<a href="'.$root.'/${2}">${2}</a>',
            $text );
        print $text;
        print "</pre>\n";
    }

    function _init_html_pre()
    {
        $h = '';
        
        preg_match('@text/html; charset=([^\s"]+)@i',$this->content_,$charset);
        if (isset($charset[1]))
        {
            $h .= <<<HTML
  <meta http-equiv="Content-Type" content="text/html; charset=${charset[1]}" />
HTML
                ;
        }
        
        preg_match('@<title>([^<]+)</title>@i',$this->content_,$title);
        if (isset($title[1]))
        {
            $h .= <<<HTML
  <title>Boost C++ Libraries - ${title[1]}</title>
HTML
                ;
        }
        else if ($h !== '')
        {
            $h .= <<<HTML
  <title>Boost C++ Libraries</title>
HTML
                ;
        }
        
        if ($h !== '')
        {
            $this->head_content_ = $h;
        }
    }
    
    function _content_html_pre()
    {
        $text = $this->content_;
        
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

    function _init_boost_book_html()
    {
        $this->_init_html_pre();
    }

    function _content_boost_book_html()
    {
        $text = $this->_content_html_pre();
        
        $text = substr($text,strpos($text,'<div class="spirit-nav">'));
        $text = substr($text,0,strpos($text,'</body>'));
        $text = str_replace('<hr>','',$text);
        $text = str_replace('<table width="100%">','<table class="footer-table">',$text);
        $text = str_replace('<table xmlns:rev="http://www.cs.rpi.edu/~gregod/boost/tools/doc/revision" width="100%">','<table class="footer-table">',$text);
        $text = preg_replace(
            '@[\s]+(border|cellpadding|cellspacing|width|height|valign|frame|rules|naturalsizeflag|background)=[^\s>]+@i',
            '',
            $text );
        /* */
        for ($i = 0; $i < 8; $i++) {
            $text = preg_replace(
                '@<img src="[\./a-z]*images/([^.]+)\.png" alt="([^"]+)"([ /]*)>@Ssm',
                '<img src="/gfx/space.png" alt="${2}" class="${1}_image" />',
                $text );
        }
        /* */
        
        print $text;
    }

    function _init_boost_libs_html()
    {
        $this->_init_html_pre();
    }

    function _content_boost_libs_html()
    {
        $text = $this->_content_html_pre();
        
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
        
        # nasty code, because (?!fubar) causes an ICE...
        preg_match('@<table@i',$text,$table_begin,PREG_OFFSET_CAPTURE);
        preg_match('@</table>@i',$text,$table_end,PREG_OFFSET_CAPTURE);
        if (isset($table_begin[0]) && isset($table_end[0]) &&
            strpos(substr($text,0,$table_end[0][1]),'boost.png',$table_begin[0][1]) !== FALSE)
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
        #if (isset($title[1]))
        #{
        #    $text = "<h1>${title[1]}</h1>\n" . $text;
        #}
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
        
        print $text;
    }

    function _init_boost_frame1_html()
    {
        $this->_init_html_pre();
    }

    function _content_boost_frame1_html()
    {
        $text = $this->_content_html_pre();
        
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
    
    function _init_simple()
    {
    }

    function _content_simple()
    {
        print $this->_content_html_pre();
    }

    function _init_404()
    {
        header("HTTP/1.0 404 Not Found");
    }

    function _content_404()
    {
        # This might also be an error extracting the file, or because we don't
        # know how to deal with the file. It would be good to give a better
        # error in those cases.

        print '<h1>404 Not Found</h1><p>File "' . $this->file_ . '"not found.</p>';
    }
}

// Return a readable error message for unzip exit state.

function unzip_error($exit_status) {
    switch($exit_status) {
    case 0: return 'No error.';
    case 1: return 'One  or  more  warning  errors  were  encountered.';
    case 2: return 'A generic error in the zipfile format was detected.';
    case 3: return 'A severe error in the zipfile format was detected.';
    case 4: return 'Unzip was unable to allocate memory for one or more buffers during program initialization.';
    case 5: return 'Unzip was unable to allocate memory or unable to obtain a tty to read the decryption password(s).';
    case 6: return 'Unzip was unable to allocate memory during decompression to disk.';
    case 7: return 'Unzip was unable to allocate memory during in-memory decompression.';
    case 9: return 'The specified zipfiles were not found.';
    case 10: return 'Invalid options were specified on the command line.';
    case 11: return 'No matching files were found.';
    case 50: return 'The disk is (or was) full during extraction.';
    case 51: return 'The end of the ZIP archive was encountered prematurely.';
    case 80: return 'The user aborted unzip prematurely with control-C (or similar).';
    case 81: return 'Testing or extraction of one or more files failed due to unsupported compression methods or unsupported decryption.';
    case 82: return 'No files were found due to bad decryption password(s).';
    default: return 'Unknown unzip error code: ' + $exit_status;
    }
}
?>
