<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	# --------------------
	# function to be called when a user is attempting to access a page that
	# he/she is not authorised to.  This outputs an access denied message then
	# re-directs to the mainpage.
	function access_denied( $p_url = null ) {
		if ( null === $p_url ) {
			global $g_logout;
			$p_url = $g_logout;
		}

		print_html_top();
		print_head_top();
		print_title( config_get( 'window_title' ) );
		print_css( config_get( 'css_inc_file' ) );
		print_head_bottom();
		print_body_top();
		print_header( config_get( 'page_title' ) );
		print_top_page( config_get( 'top_page_inc' ) );
		echo '<div class="warning">';
		echo '<div align="center">Access Denied<br /><br />';
		print_bracket_link( $p_url, lang_get( 'proceed' ) );
		print '</div></div>';
		print_bottom_page( config_get( 'bottom_page_inc' ) );
		print_footer( __FILE__ );
		print_body_bottom();
		print_html_bottom();
		exit;
	}
	# --------------------
	# Check to see that the unique identifier is really unique
	function check_cookie_string_duplicate( $p_cookie_string ) {
		global $g_phpWN_user_table;

		$c_cookie_string = addslashes($p_cookie_string);

		$query = "SELECT COUNT(*)
				FROM $g_phpWN_user_table
				WHERE cookie_string='$c_cookie_string'";
		$result = db_query( $query );
		$t_count = db_result( $result, 0, 0 );
		return ( $t_count > 0 );
	}	
	# --------------------
	# This string is used to use as the login identified for the web cookie
	# It is not guarranteed to be unique and should be checked
	# The string returned should be 64 characters in length
	function generate_cookie_string() {
		$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
		$t_val = md5( $t_val ) . md5( time() );
		return substr( $t_val, 0, 64 );
	}
	# --------------------
	# The string returned should be 64 characters in length
	function create_cookie_string() {
		$t_cookie_string = generate_cookie_string();
		while ( check_cookie_string_duplicate( $t_cookie_string ) ) {
			$t_cookie_string = generate_cookie_string();
		}
		return $t_cookie_string;
	}
	### --------------------
	function access_encrypt_password( $p_password ) {
		switch( config_get( 'auth_type' ) ) {
			case AUTH_PLAIN: 
				$t_password = $p_password;
				break;

			case AUTH_CRYPT: 
				$salt = substr( $p_password, 0, 2 );
				$t_password = crypt( $p_password, $salt );
				break;

			case AUTH_MD5:
				$t_password = md5( $p_password );
				break;

			default:
				# @@@@ Replace with proper error
				echo "Invalid authentication type";
				exit;
		} // switchconfig_get()) {

		return substr( $t_password, 0, 32 );
	}
	### --------------------
	function password_match( $p_test_password, $p_password ) {
		return ( access_encrypt_password( $p_test_password ) === $p_password );
	}
	### --------------------
	function access_verify_login( $p_username, $p_password )	{
		global $g_phpWN_user_table;

		$c_username = db_prepare_string( $p_username );

		### get user info
		$query = "SELECT *
				FROM $g_phpWN_user_table
				WHERE username='$c_username' AND enabled=1";
		$result = db_query( $query );
		$row = db_fetch_array( $result );

		if ( $row ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );
		} else {
			### invalid login, retry
			return (false);
		}

		return ( password_match( $p_password, $v_password ) );
	}
	### --------------------
	function create_random_password( $p_email ) {
		mt_srand( time() );
		$t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
		return substr( crypt( md5( $p_email.$t_val ) ), 0, 12 );
	}
	### --------------------
	function is_moderator() {
		global 	$g_string_cookie_val, $g_phpWN_user_table;

		$query = "SELECT COUNT(*)
				FROM $g_phpWN_user_table
				WHERE cookie_string='$g_string_cookie_val'";
		$result = db_query( $query );
		$count = db_result( $result, 0, 0 );

		return $count;
	}
	### --------------------
	function access_is_logged_in() {
		global $g_string_cookie_val;

		### if logged in
		if ( isset( $g_string_cookie_val ) ) {
			return ( !empty( $g_string_cookie_val ) );
		}

		### not logged in
		return false;
	}
	### --------------------
	### checks to see that a user is logged in
	### if the user is and the account is enabled then let them pass
	### otherwise redirect them to the login page
	function login_cookie_check( $p_redirect_url = '' ) {
		global $g_string_cookie_val, $g_login_page, $g_logout;

		### if logged in
		if ( isset( $g_string_cookie_val ) ) {
			if ( empty( $g_string_cookie_val ) ) {
				util_header_redirect( $g_login_page );
			}

			### go to redirect
			if ( !empty( $p_redirect_url ) ) {
				util_header_redirect( $p_redirect_url );
			}
			### continue with current page
			else {
				return;
			}
		}
		### not logged in
		else {
			util_header_redirect( $g_login_page );
		}
	}
	### --------------------
	# Make sure that the specified action can be done by the logged-in user
	# true: allowed
	# false: not allowed
	# if for this action a threshold is defined, it will be used.
	# if the threshold is set to NOBODY, the specified set of user types will be used.
	# if action is unknown, then it will return false
	function access_check_action( $p_action ) {
		global $g_string_cookie_val, $g_access_levels, $g_access_sets;

		if ( !isset( $g_access_levels[$p_action] ) ) {
			return false;
		}

		if ( empty( $g_string_cookie_val ) ) {
			$t_access_level = ANONYMOUS;
		} else {
			$t_user = user_get_info( user_where_current() );
			if ( false === $t_user ) {
				return false;
			}

			$t_access_level = $t_user['access_level'];
		}

		if ( NOBODY !== $g_access_levels[$p_action] ) {
			return ( $t_access_level >= $g_access_levels[$p_action] );
		}

		if ( !isset( $g_access_sets[$p_action] ) ) {
			return false;
		}

		return ( in_array( $t_access_level, $g_access_sets[$p_action] ) );
	}
	### --------------------
	function access_ensure_check_action( $p_action, $p_url = null ) {
		if ( access_check_action( $p_action ) ) {
			return;
		}

		access_denied( $p_url );
	}
?>