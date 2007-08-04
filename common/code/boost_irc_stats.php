<?php
/*
  Copyright 2005-2006 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost.php');

class boost_irc_stats
{
    var $head_content_ = NULL;
    var $content_ = NULL;
    
    function boost_irc_stats($uri)
    {
        $context = NULL;
        $this->content_ = file_get_contents($uri,false,$context);
        
        if ($this->content_ && $this->content_ != '')
        {
            $this->_init_html();
        }
        
        $this->head_content_ .= <<<HTML
  
  <!-- IRC STATS URI == '${uri}' -->
  
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
        
        preg_match('@<body>@i',$text,$body_begin,PREG_OFFSET_CAPTURE);
        preg_match('@</body>@i',$text,$body_end,PREG_OFFSET_CAPTURE);
        //~ preg_match('@<div align="center">@i',$text,$body_begin,PREG_OFFSET_CAPTURE);
        //~ preg_match('@</div>\s+</body>@i',$text,$body_end,PREG_OFFSET_CAPTURE);
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
            '@src="[^/]+/([^"\s]*)\.png"@i',
            'class="irc-stats-${1}" src="/gfx/space.png"',
            $text );
        $text = preg_replace(
            '@>([0-9]+)(\.[0-9]+)?%(<br />)?<@i',
            '><span class="irc-stats-percent">${1}<span class="irc-stats-decimal">${2}%</span></span><',
            $text );
        $text = preg_replace(
            '@<[/]?(font|hr)[^>]*>@i',
            '',
            $text );
        $text = preg_replace(
            '@<td>&nbsp;</td>@i',
            '<td class="empty">&nbsp;</td>',
            $text );
        $text = preg_replace(
            '@[\s]+(border|cellpadding|cellspacing|valign|align|frame|rules|naturalsizeflag|background|wrap)=[^\s>]+@i',
            '',
            $text );
        $text = preg_replace(
            '@[\s]+width="?(100%|710|520|714)"?@i',
            '',
            $text );
        
        print $text;
    }
}
?>
