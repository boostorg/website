<?php
/*
  Copyright 2005-2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost_config.php');


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

class boost_wiki
{
    var $head_content_ = NULL;
    var $content_ = NULL;
    
    function boost_wiki($uri)
    {
        $context = NULL;
        if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST")
        {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'content' => file_get_contents("php://input"),
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                    )
                ));
        }
        else if (isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"])
        {
            $uri .= '?';
            if (isset($_SERVER["PATH_INFO"]) && $_SERVER["PATH_INFO"] != '/')
            {
                $uri .= 'id='.substr($_SERVER["PATH_INFO"],1).'&';
            }
            $uri .= $_SERVER["QUERY_STRING"];
        }
        else if (isset($_SERVER["PATH_INFO"]) && $_SERVER["PATH_INFO"] != '/')
        {
            $uri .= '?'.substr($_SERVER["PATH_INFO"],1);
        }
        $this->content_ = file_get_contents($uri,false,$context);
        
        if ($this->content_ && $this->content_ != '')
        {
            $this->_init_html();
        }
        
        $this->head_content_ .= <<<HTML
  
  <!-- WIKI URI == '${uri}' -->
  
HTML
            ;
    }
    
    function content_head()
    {
        print $this->head_content_;
    }
    
    function content()
    {
        if ($this->content_ && $this->content_ != '')
        {
            $this->_content_html();
        }
    }

    function _init_html()
    {
        preg_match('@text/html; charset=([^\s"]+)@i',$this->content_,$charset);
        if (isset($charset[1]))
        {
            $this->head_content_ .= <<<HTML
  <meta http-equiv="Content-Type" content="text/html; charset=${charset[1]}" />
HTML
                ;
        }
        else
        {
            $this->head_content_ .= <<<HTML
  <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
HTML
                ;
        }
        
        preg_match('@<title>([^<]+)</title>@i',$this->content_,$title);
        if (isset($title[1]))
        {
            $this->head_content_ .= <<<HTML
  <title>Boost C++ Libraries - ${title[1]}</title>
HTML
                ;
        }
        else
        {
            $this->head_content_ .= <<<HTML
  <title>Boost C++ Libraries - Wiki</title>
HTML
                ;
        }
    }
    
    function _content_html()
    {
        $text = $this->content_;
        
        $text = preg_replace(
            '@href="?http://www.boost.org/?([^"\s]*)"?@i',
            'href="/${1}"',
            $text );
        $text = preg_replace(
            '@href="?(?:\.\./)+people/(.*\.htm)"?@i',
            'href="/users/people/${1}l"',
            $text );
        $text = preg_replace(
            '@href="?(?:\.\./)+(LICENSE_.*\.txt)"?@i',
            'href="/${1}"',
            $text );
        $text = preg_replace(
            '@<a\s+(class="[^"]+")?\s*href="?(http|mailto)(:[^"\s]*)"?@i',
            '<a class="external" href="${2}${3}"',
            $text );
        $text = preg_replace(
            '@href="?wiki.pl[?]((?:action|search)=[^"\s]*)"?@i',
            'href="/doc/wiki/?${1}"',
            $text );
        $text = preg_replace(
            '@href="?wiki.pl[?]?([^"\s]*)"?@i',
            'href="/doc/wiki/${1}"',
            $text );
        
        switch (isset($_REQUEST["action"]) ? $_REQUEST["action"] : 'display')
        {
            case 'edit':
            preg_match('@<hr>@i',$text,$body_begin,PREG_OFFSET_CAPTURE);
            preg_match('@</textarea>@i',$text,$body_end,PREG_OFFSET_CAPTURE);
            break;
            
            case 'post':
            preg_match('@<hr>@i',$text,$body_begin,PREG_OFFSET_CAPTURE);
            preg_match('@</body>@i',$text,$body_end,PREG_OFFSET_CAPTURE);
            break;
            
            default:
            preg_match('@<hr>@i',$text,$body_begin,PREG_OFFSET_CAPTURE);
            preg_match('@<form method="post" action="wiki.pl" [^>]*>@i',$text,$body_end,PREG_OFFSET_CAPTURE);
            break;
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
        
        $text = preg_replace(
            '@<[/]?(font|hr)[^>]*>@i',
            '',
            $text );
        $text = preg_replace(
            '@[\s]+(border|cellpadding|cellspacing|width|height|valign|align|frame|rules|naturalsizeflag|background|wrap)=[^\s>]+@i',
            '',
            $text );
        
        $edit_html = <<<HTML
</textarea>
<label><span class="wiki-label">Summary</span> <input type="text" name="summary" value="*" size="60" maxlength="200" id="summary" /></label>
<label><span class="wiki-label">Minor Edit</span> <input type="checkbox" name="recent_edit" value="on" id="recent_edit" /></label>
<input type="submit" name="Save" value="Save" id="Save" />
<input type="submit" name="Preview" value="Preview" id="Preview" />
</form>
HTML;
        switch (isset($_REQUEST["action"]) ? $_REQUEST["action"] : 'display')
        {
            case 'edit':
            $text = preg_replace(
                '@<form method="post" action="wiki.pl" enctype="application/x-www-form-urlencoded">@i',
                '<form method="post" action="./?action=post" enctype="application/x-www-form-urlencoded" id="wiki-edit-form">',
                $text );
            $text .= $edit_html;
            break;
            
            case 'post':
            $text = preg_replace(
                '@<form method="post" action="wiki.pl" enctype="application/x-www-form-urlencoded">@i',
                '<form method="post" action="./?action=post" enctype="application/x-www-form-urlencoded" id="wiki-edit-form">',
                $text );
            $text = preg_replace(
                '@<h2>Preview only, not yet saved</h2>([^\r\n]*[\r\n]*)*@i',
                '</div>',
                $text );
            $text = preg_replace(
                '@</textarea>([^\r\n]*[\r\n]*){3}@i',
                $edit_html . <<<HTML
<div class="clear"></div>
<h2 class="content-header">Preview only, not yet saved</h2>
<div id="wiki-preview">
HTML
                ,$text );
            break;
            
            default:
            break;
        }
        
        print $text;
    }
}
?>
