<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	require_once( 'core' . DIRECTORY_SEPARATOR . 'api.php' );	

	$f_username = gpc_get_string( 'f_username' );
	$f_password = gpc_get_string( 'f_password' );
	$f_perm_login = gpc_get_string( 'f_perm_login', 'off' );

	$row = user_get_info( user_where_username_equals_and_enabled( $f_username ) );

	if ( $row ) {
		extract( $row, EXTR_PREFIX_ALL, 'v' );
	} else {
		### invalid login, retry
		util_header_redirect( "$g_login_page?f_msg=error" );
	}

	if( password_match( $f_password, $v_password ) ) {
		### set permanent cookie (1 year)
		if ( ( isset( $f_perm_login ) ) && ( $f_perm_login == "on" ) ) {
			if ( !setcookie( $g_string_cookie, $v_cookie_string, time() + $g_cookie_time_length, $g_cookie_url ) ) {
				# @@@@ Proper error message
				echo "Unable to set cookie";
				exit;
			}
		}
		### set temp cookie, cookie dies after browser closes
		else {
			if ( !setcookie( $g_string_cookie, $v_cookie_string, 0, $g_cookie_url ) ) {
				# @@@@ Proper error message
				echo "Unable to set cookie";
				exit;
			}
		}

		util_header_redirect( $g_admin_page );
	}
	else {
		### invalid login, retry
		util_header_redirect( "$g_login_page?f_msg=error" );
	}
?>