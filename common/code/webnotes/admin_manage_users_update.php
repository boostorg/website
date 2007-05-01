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

	access_ensure_check_action( ACTION_USERS_EDIT );

	$f_user_id				= gpc_get( 'f_user_id' );
	$f_email				= gpc_get( 'f_email' );
	$f_password				= gpc_get( 'f_password' );
	$f_password_confirm		= gpc_get( 'f_password_confirm' );
	$f_access_level			= gpc_get( 'f_access_level' );

	if ( $f_password != $f_password_confirm ) {
		util_header_redirect( $g_admin_manage_users_edit.'?f_user_id='.$f_user_id );
	}

	if ( isset( $f_enabled ) ) {
		$f_enabled = 1;
	} else {
		$f_enabled = 0;
	}

	if ( isset( $f_protected ) ) {
		$f_protected = 1;
	} else {
		$f_protected = 0;
	}

	user_update( $f_user_id, $f_email, $f_password, $f_access_level, $f_enabled, $f_protected );

	util_header_redirect( $g_admin_manage_users );
?>