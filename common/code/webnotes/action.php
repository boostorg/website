<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	require_once( 'core' . DIRECTORY_SEPARATOR . 'api.php' );

	login_cookie_check();

	if ( !isset( $f_action ) ) {
		echo 'f_action not defined<br />';
		exit;
	}

	# @@@@ add handling for confirm?

	# The access level check is done in the APIs
	if ( isset( $f_note_id )) {
		$t_note_info = note_get_info( note_where_id_equals( $f_note_id ) );
		if ( false === $t_note_info ) {
			echo "note not found";
			exit;
		}
		
		$t_page_info = page_get_info( page_where_id_equals( $t_note_info['page_id'] ) );
		if ( false === $t_page_info ) {
			echo "page not found";
			exit;
		}
		$t_url = $t_page_info['url'];

		if ( 'accept' === $f_action ) {
			note_accept( $f_note_id );
		} else if ( 'decline' === $f_action ) {
			note_decline( $f_note_id );
		} else if ( 'archive' === $f_action ) {
			note_archive( $f_note_id );
		} else if ( 'delete' === $f_action ) {
			note_delete( $f_note_id );
		} else if ( 'pack' === $f_action ) {
			# in this case id = 0
			note_pack_deleted();
		} else if ( 'queue' === $f_action ) {
			note_pending( $f_note_id );
		} else if ( 'edit' === $f_action ) {
			util_header_redirect( "$g_note_add_page?f_note_id=$f_note_id" );
		}
	}

	# The access level check is done in the APIs
	if ( isset( $f_page_id ) ) {
		$c_page_id = stripslashes( urldecode( $f_page_id ) );
		if ( 'unindex' === $f_action ) {
			page_delete( $c_page_id );
		}
		if ( 'index' === $f_action ) {
			page_add( $c_page_id );
		}
		
		$t_url = $HTTP_REFERER;
	}

	if ( isset( $f_wait ) ) {
		print_html_top();
		print_head_top();
		print_title( $g_window_title );
		print_css( $g_css_inc_file );
		print_head_bottom();
		print_body_top();
		print_header( $g_page_title );
		print_top_page( $g_top_page_inc );

		print_admin_menu();

		echo "<br /><div align=\"center\">Operation Successful<br /><a href=\"$t_url\">[ Click here to proceed ]</a></div><br />";

		print_footer( __FILE__ );
		print_bottom_page( $g_bottom_page_inc );
		print_body_bottom();
		print_html_bottom();
	} else {
		util_header_redirect( $t_url );
	}
?>