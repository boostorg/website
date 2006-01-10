<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	###########################################################################
	# Link API
	###########################################################################

	### --------------------
	function link_create( $p_url, $p_caption, $p_link_active=true, $p_prefix = '[ ', $p_suffix = ' ]' ) {
		if ( !empty( $p_url ) || (false === p_link_active) ) {
			return "$p_prefix<a href=\"$p_url\">$p_caption</a>$p_suffix";
		} else {
			return "$p_prefix$p_caption$p_suffix";
		}
	}
	### --------------------
	function link_note_action( $p_note_id, $p_action, $p_url, $p_link_active = true, $p_caption = null ) {
		if ( null === $p_caption ) {
			$t_caption = lang_get( 'action_' . $p_action );
			$t_before = '[ ';
			$t_after = ' ]';
		} else {
			$t_caption = $p_caption;
			$t_before = $t_after = '';
		}

		$c_note_id = db_prepare_int( $p_note_id );
		$c_action = urlencode( $p_action );
		# $c_url = urlencode( $p_url );
		$t_action = config_get( 'web_directory') . 'action.php';
		$t_link = "$t_action?f_action=$c_action&amp;f_note_id=$c_note_id"; # &amp;f_url=$c_url";

		return( link_create( $t_link, $t_caption, $p_link_active, $t_before, $t_after ) );
	}
	### --------------------
	# $p_page = $p_page_id if action is unindex
	# $p_page = $p_page_name if action is index
	function link_page_action( $p_page, $p_action, $p_url, $p_link_active = true, $p_caption = null ) {
		if ( null === $p_caption ) {
			$t_caption = lang_get( 'action_' . $p_action );
			$t_before = '[ ';
			$t_after = ' ]';
		} else {
			$t_caption = $p_caption;
			$t_before = $t_after = '';
		}

		$c_page_id = urlencode( $p_page );
		$c_action = urlencode( $p_action );
		$c_url = urlencode( $p_url );
		$t_action = config_get( 'web_directory' ) . 'action.php';
		$t_link = "$t_action?f_action=$c_action&amp;f_page_id=$c_page_id&amp;f_url=$c_url";

		return( link_create( $t_link, $t_caption, $p_link_active, $t_before, $t_after ) );
	}
?>