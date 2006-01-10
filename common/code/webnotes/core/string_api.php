<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	### --------------------
	function string_safe( $p_string ) {
		return addslashes( $p_string );
	}
	### --------------------
	function string_unsafe( $p_string ) {
		return stripslashes( $p_string );
	}
	### --------------------
	function string_display( $p_string ) {
		return htmlspecialchars(stripslashes( $p_string ));
	}
	### --------------------
	function string_display_with_br( $p_string ) {
		return str_replace( "&lt;br&gt;", "<br />", htmlspecialchars(stripslashes( $p_string )));
	}
	### --------------------
	function string_edit( $p_string ) {
		return str_replace( "<br>", "",  stripslashes( $p_string ) );
	}
	### --------------------
	# return just the URL portion of the file path
	function string_get_url( $p_page ) {
		global $DOCUMENT_ROOT;
		return substr( $p_page, strlen($DOCUMENT_ROOT), strlen($p_page));
	}
	### --------------------
	function string_preserve_spaces( $p_string ) {
		$p_string = str_replace( "\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $p_string );
		return str_replace( " ", "&nbsp;", $p_string );
	}
	### --------------------
	# Preserve spaces at beginning of lines.
	function string_preserve_spaces_at_bol( $p_string ) {
		$lines = explode("\n", $p_string);
		for ( $i = 0; $i < count( $lines ); $i++ ) {
			$count = 0;
			$prefix = '';
			while ( substr($lines[$i], $count, 1) == ' ' ) {
			  $count++;
			}
			for ($j = 0; $j < $count; $j++) {
			  $prefix .= '&nbsp;';
			}
			$lines[$i] = $prefix . substr( $lines[$i], $count );

		}
		$result = implode( "\n", $lines );
		return $result;
	}
	### --------------------
	function string_to_form( $p_string ) {
		return htmlspecialchars( addslashes( $p_string ) );
	}
	### --------------------
	function string_add_note_links( $p_page_url, $p_note ) {
		return ( preg_replace( '/#([0-9]+)/', "<a href=\"$p_page_url#\\1\">#\\1</a>", $p_note ) );
	}
	### --------------------
	function string_emotions( $p_note ) {
		if ( OFF == config_get( 'enable_smileys' ) ) {
		    return $p_note;
		}

		$images_dir = config_get( 'web_directory' ) . 'images/';

		$smile = '<img src="' . $images_dir . 'smile.gif" width="15" height="15" alt=":)" />';
		$sad = '<img src="' . $images_dir . 'sad.gif" width="15" height="15" alt=":(" />';
		$wink = '<img src="' . $images_dir . 'wink.gif" width="15" height="15" alt=";)" />';
		$big_smile = '<img src="' . $images_dir . 'bigsmile.gif" width="15" height="15" alt=":D" />';
		$cool = '<img src="' . $images_dir . 'cool.gif" width="15" height="15" alt="8-D" />';
		$mad = '<img src="' . $images_dir . 'mad.gif" width="15" height="15" alt=">-(" />';
		$shocked = '<img src="' . $images_dir . 'shocked.gif" width="15" height="15" alt=":-*" />';

		$p_note = str_replace( ':)', $smile, $p_note );
		$p_note = str_replace( ':-)', $smile, $p_note );
		$p_note = str_replace( ':(', $sad, $p_note );
		$p_note = str_replace( ':-(', $sad, $p_note );
		$p_note = str_replace( ';)', $wink, $p_note );
		$p_note = str_replace( ';-)', $wink, $p_note );
		$p_note = str_replace( ':D', $big_smile, $p_note );
		$p_note = str_replace( ':-D', $big_smile, $p_note );
		$p_note = str_replace( '8-)', $cool, $p_note );
		$p_note = str_replace( '&gt;-(', $mad, $p_note );
		$p_note = str_replace( ':-*', $shocked, $p_note );

		return ( $p_note );
	}
	### --------------------
	function string_hyperlink( $p_note_string ) {
		$p_note_string = preg_replace("/(http:\/\/[0-9a-zA-Z\-\._\/\?=]+)/", "<a href=\"\\1\">\\1</a>", $p_note_string);
		$p_note_string = preg_replace("/(mailto:[0-9a-zA-Z\-\._@]+)/", "<a href=\"\\1\">\\1</a>", $p_note_string);
		return ($p_note_string);
	}
	### --------------------
	function string_icq_status( $p_note_string ) {
	  return (preg_replace("/icq:\/\/([0-9]+)/", "<a href=\"http://web.icq.com/wwp?Uin=\\1\">\\0<img src=\"http://web.icq.com/whitepages/online?icq=\\1&img=5\" width=\"18\" height=\"18\" /></a>", $p_note_string ));
	}
	### --------------------
	function string_prepare_note_for_viewing( $p_note_string, $p_url = null ) {
		$p_note_string = htmlspecialchars( $p_note_string );
		$p_note_string = string_preserve_spaces_at_bol( $p_note_string );
		$p_note_string = string_hyperlink( $p_note_string );
		$p_note_string = string_icq_status( $p_note_string );
		if ( null !== $p_url ) {
			$p_note_string = string_add_note_links( $p_url, $p_note_string );
		}

		$p_note_string = string_emotions( $p_note_string );
		return ($p_note_string);
	}
	### --------------------
?>