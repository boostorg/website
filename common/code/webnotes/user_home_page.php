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

	access_ensure_check_action( ACTION_USERS_EDIT_OWN );

	$row = user_get_info( user_where_current() );
	extract( $row, EXTR_PREFIX_ALL, "v" );

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	print_admin_menu();
	$t_access_level = enum_get_element( 'access_levels', $v_access_level );
	echo "<p>Logged in as $v_username ($t_access_level)</p>";

	print_bottom_page( $g_bottom_page_inc );
	print_footer( __FILE__ );
	print_body_bottom();
	print_html_bottom();
?>