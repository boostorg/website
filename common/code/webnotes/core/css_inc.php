<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------
?>
<style type="text/css">

div.pwn input {	
	background:	 	#dcdcdc;
	color: 			#000000;
	font-size:		11px;
	border-style: 		solid;
	border-color: 		#b1b1b1;
	border-width: 		thin;
}


div.pwn textarea {
	background:	 	#dcdcdc;
	color:			#000;
        font-size:              11px;
	border-style: 		solid;
	border-color: 		#000;
	border-width: 		1px;
	overflow: 		auto;
}

div.pwn select {
        background:	       #ccc;
        color:                  #000;   
        font-size:              11px; 
        border-style:           none;
        border-color:           #fff;
        border-width:           0;
}

div.pwn { font-family:Verdana, Arial; font-size: 10pt; background: transparent; }
div.pwn td { font-family:Verdana, Arial; font-size: 10pt; padding: 4px; }
div.pwn th { font-family:Verdana, Arial; font-size: 10pt; padding: 4px; }
div.pwn p { font-family:Verdana, Arial; font-size: 10pt; }
div.pwn div.spacer { width: auto; border: none; margin: 20px; }

div.pwn .large-width { width: 100%; text-align: center; }
div.pwn .medium-width { width: 75%; text-align: center; }
div.pwn .small-width { width: 50%; text-align: center; }
div.pwn .center { text-align: center; }
div.pwn div.title { background-color: #98b8e8; padding: 3px; border-bottom: 5px solid #000000; font-size: 10pt; font-weight: bold; letter-spacing: 1.0em; text-align: right; color: #204888; padding-top: 10px; padding-bottom: 10px; margin-bottom: 0px; }
div.pwn div.menu { background-color: #f4f4f4; border-bottom: 1px solid #000000; padding: 3px; text-align: left; margin-bottom: 20px; padding-left: 10px; }
div.pwn div.menu a { text-decoration: none; color: #666; }
div.pwn div.menu a:hover { text-decoration: underline; color: #000000; }
div.pwn div.footer {background-color: #ffffff; border-top: 1px solid #222222; padding: 3px; font-size: 10pt; text-align: left; color: #000000; margin-top: 20px; }
div.pwn div.top-file { }
div.pwn div.bottom-file { }
div.pwn div.warning {background-color: #f8e0e0; border: 1px solid #aa4444; padding: 8px; margin-top: 10px; margin-bottom: 10px; }
div.pwn div.error {background-color: #f8e0e0; border: 1px solid #aa4444; padding: 8px; margin-top: 10px; margin-bottom: 10px; }
div.pwn span.copyright { font-style: italic; }
div.pwn span.version { font-style: italic; }
div.pwn th, div.pwn .category { background-color: #c8c8e8; color: #000000; font-weight: bold; }
div.pwn form { margin: 0px; display: inline; }
div.pwn address { font-family:Verdana, Arial; font-size: 8pt; }
div.pwn table.box { border: solid 1px #000000; margin-top: 10px; margin-bottom: 10px; text-align: left; width: 100%; }
div.pwn tr.row-1 { background-color: #d8d8d8; color: #000000; }
div.pwn tr.row-2 { background-color: #e8e8e8; color: #000000; }
div.pwn tr.title { color: #000000; font-weight: bold; }
div.pwn tr.buttons { color: #000000; text-align: center; }
div.pwn div.note p.title { font-weight: bold; text-align: center; }
div.pwn div.note { background-color: #c8e0f8; border: 1px solid #4444aa; padding: 8px; margin-top: 10px; margin-bottom: 10px; }

/*
h3 { font-family:Verdana, Arial; font-size: 13pt; font-weight: bold; text-align: center }
div {width: auto; font-size: 10pt; clear: both;}
div.code {background-color: #f0f0f0; border: 1px solid #444444; padding: 8px; font-family: courier new, courier, fixed; white-space: pre;}
div.note {background-color: #c8e0f8; border: 1px solid #4444aa; padding: 8px;}
div.warning {background-color: #f8e0e0; border: 1px solid #aa4444; padding: 8px;}
div.parent {background-color: #e8e8e8; border-bottom: 1px solid #aaaaaa; padding-top: 4px;}
div.example {background-color: #f4f4f4; font-family: courier new, courier, fixed; border-left: 1px solid #000000; border-right: 1px solid #000000; display: inline;}
div.box { border: 1px solid #000000; padding: 8px;}
*/
</style>