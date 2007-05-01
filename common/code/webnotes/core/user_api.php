<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	###########################################################################
	### USER API                                                            ###
	###########################################################################

	### --------------------
	function user_create( $p_username, $p_password, $p_email, $p_access_level = null, $p_enabled = true, $p_protected = false ) {
		if ( false !== user_get_info( user_where_username_equals( $p_username ) ) ) {
			echo "<p>Duplicate user.</p>";
			return false;
		}

		if ( false !== user_get_info( user_where_email_equals( $p_email ) ) ) {
			echo "<p>Duplicate email.</p>";
			return false;
		}

		if ( null === $p_access_level ) {
		    $p_access_level = REGISTERED;   # @@@@ Move to config.
		}

		$c_username				= db_prepare_string( $p_username );
		$c_email				= db_prepare_string( $p_email );
		$c_encrypted_password	= db_prepare_string( access_encrypt_password( $p_password ) );
		$c_enabled				= db_prepare_int( $p_enabled );
		$c_protected			= db_prepare_int( $p_protected );

		$t_seed = $p_email . $p_username;
		$t_cookie_string = create_cookie_string( $t_seed );
		$c_cookie_string = db_prepare_string( $t_cookie_string );

		$query = "INSERT INTO phpWN_user_table (username, password, email, cookie_string, access_level, enabled, protected)
					VALUES ('$c_username', '$c_encrypted_password', '$c_email', '$c_cookie_string', $p_access_level, $c_enabled, $c_protected)";
		$result = mysql_query($query);

		return( false !== $result );
	}
	### --------------------
	function user_signup( $p_username, $p_email ) {
		# Check to see if signup is allowed
		if ( OFF == config_get( 'allow_signup' ) ) {
			return false;
		}

		if ( empty( $p_username ) || empty( $p_email ) ) {
		    return false;
		}

		$t_password = create_random_password( $p_email );

		if ( false === user_create( $p_username, $t_password, $p_email ) ) {
		    return false;
		}

		email_signup($p_username, $t_password, $p_email);

		return true;
	}
	### --------------------
	function user_where_current( ) {
		global $g_string_cookie_val;
		return ( user_where_cookie_equals( $g_string_cookie_val ) );
	}
	### --------------------
	function user_where_id_equals( $p_id ) {
		$c_id = db_prepare_int( $p_id );
		return ("(id='$c_id')");
	}
	### --------------------
	function user_where_username_equals( $p_username ) {
		$c_username = db_prepare_string( $p_username );
		return ("(username='$c_username')");
	}
	### --------------------
	function user_where_username_equals_and_enabled( $p_username ) {
		$c_username = db_prepare_string( $p_username );
		return ("((username='$c_username') AND (enabled=1))");
	}
	### --------------------
	function user_where_email_equals( $p_email ) {
		$c_email = db_prepare_string( $p_email );
		return ("(email='$c_email')");
	}
	### --------------------
	function user_where_cookie_equals( $p_cookie ) {
		$c_cookie = db_prepare_string( $p_cookie );
		return ("(cookie_string='$c_cookie')");
	}
	### --------------------
	# The parameter passed to this function is constructed via user_where_*().
	# $p_where is not cleaned, since it is assume that all the necessary escaping is
	# done in the function that constructed the where statement.
	function user_get_info( $p_where ) {
		$query = "SELECT *
				FROM " . config_get( 'phpWN_user_table' ) . "
				WHERE $p_where
				LIMIT 1";

		$result = db_query( $query );
		if ( false === $result ) {
			return false;
		}

		$row = db_fetch_array( $result );
		if ( false === $row ) {
			return false;
		}

		return $row;
	}
	### --------------------
	function user_get_all() {
		global $g_phpWN_user_table;

		$t_users_array = array();

		$query = "SELECT *
			FROM $g_phpWN_user_table";
		$result = db_query( $query );
		if ( !$result ) {
			return false;
		}

		while ( $row = db_fetch_array( $result ) ) {
			$t_users_array[] = $row;
		}

		return $t_users_array;
	}
	### --------------------
	function user_get_row( $p_user_id ) {
		global $g_phpWN_user_table;

		$t_users_array = array();

		$query = "SELECT *
			FROM $g_phpWN_user_table
			WHERE id='$p_user_id'";
		$result = db_query( $query );
		if ( !$result ) {
			return false;
		}

		return db_fetch_array( $result );
	}
	### --------------------
	# $p_where is constructed using user_where_*().
	function user_change_password( $p_where, $p_old_password, $p_new_password, $p_verify_password = null ) {
		$t_user = user_get_info( $p_where );
		if ( false === $t_user ) {
			return false;  ## error message printed by user_get_info().
		}

		if ( !access_verify_login( $t_user['username'], $p_old_password ) ) {
			echo 'Original password is incorrect.<br />';
			return false;
		}

		if ( ( $p_verify_password !== null ) && ( $p_verify_password != $p_new_password ) ) {
			echo 'New and verify passwords do not match.<br />';
			return false;
		}

		$t_password = access_encrypt_password( $p_new_password );
		$c_password = db_prepare_string( $t_password );

		$query = "UPDATE " . config_get( 'phpWN_user_table' ) . "
				SET password='$c_password'
				WHERE $p_where";
		$result = db_query( $query );
		if ( false === $result ) {
			return false;
		}

		return true;
	}
	### --------------------
	# we assume that the password has been checked for accuracy
	# we assume that the enabled value is 0 or 1
	function user_update( $p_user_id, $p_email, $p_password, $p_access_level, $p_enabled, $p_protected ) {
		global $g_phpWN_user_table;

		if ( empty( $p_password ) ) {
			$t_user_row = user_get_row( $p_user_id );
			$c_password = $t_user_row['password'];
		} else {
			$c_password = db_prepare_string( access_encrypt_password( $p_password ) );
		}

		$c_user_id				= db_prepare_int( $p_user_id );
		$c_email				= db_prepare_string( $p_email );
		$c_access_level			= db_prepare_string( $p_access_level );
		$c_enabled				= db_prepare_string( $p_enabled );
		$c_protected			= db_prepare_string( $p_protected );

		$query = "UPDATE $g_phpWN_user_table
				SET email='$c_email',
					password='$c_password',
					access_level=$c_access_level,
					enabled=$c_enabled,
					protected=$c_protected
				WHERE id=$c_user_id";
		return db_query( $query );
	}
	### --------------------
	function user_delete( $p_user_id ) {
		global $g_phpWN_user_table;

		$c_user_id = db_prepare_int( $p_user_id );

		$query = "DELETE FROM $g_phpWN_user_table
				WHERE id=$c_user_id";
		return db_query( $query );
	}
	### --------------------
?>