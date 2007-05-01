<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	require_once( 'core' . DIRECTORY_SEPARATOR . 'api.php' );

	access_ensure_check_action( ACTION_NOTES_SUBMIT );

	$f_page_id = gpc_get_int( 'f_page_id' );
	$f_note_id = gpc_get_int( 'f_note_id' );
	$f_email = stripslashes( gpc_get_string( 'f_email' ) );
	$f_note = stripslashes( gpc_get_string( 'f_note' ) );

	### insert note
	if ( 0 == $f_note_id ) {
		$result = note_add( $f_page_id, $f_email, $REMOTE_ADDR, $f_note);
		if ( $result !== false ) {
			email_note_added( $result );
		}
	} else {
		$result = note_update( $f_note_id, $f_email, $f_note );
		email_note_updated( $f_note_id );
	}
	
	$t_page_info = page_get_info( page_where_id_equals( $f_page_id ) );
	if ( false === $t_page_info ) {
	    echo "page not found";
		exit;
	}
	
	$t_url = $t_page_info['url'];

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );

	if ( $result ) {
		print_meta_redirect( $t_url, $g_time_wait );
	}

	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );

	echo '<div align="center">';
	if ( $result ) {
		if ( 0 == $f_note_id ) {
			echo "<p>$s_created_note_msg</p>";
		} else {
			echo "<p>Note modified successfully</p>";
		}
	}
	else {
		echo "$s_sql_error_msg <a href=\"$g_administrator_email\">$s_administrator</a><br />";
	}

	echo "<p><a href=\"$t_url\">$s_click_to_proceed_msg</a></p>";
	echo '</div>';

	print_footer( __FILE__ );
	print_body_bottom();
	print_html_bottom();
?>