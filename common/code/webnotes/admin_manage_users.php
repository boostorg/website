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

	access_ensure_check_action( ACTION_USERS_MANAGE );

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	print_admin_menu();

	$t_users_array = user_get_all();

	echo '<table class="box" summary="">';
	echo '<thead><tr><th>Username</th><th>Email</th><th>Access Level</th><th>Enabled</th><th>Protected</th></tr></thead><tbody>';
	$i = 0;
	foreach ( $t_users_array as $user ) {
		extract( $user, EXTR_PREFIX_ALL, 'v' );
		$v_enabled = $v_enabled ? 'x' : '&nbsp;';
		$v_protected = $v_protected ? 'x' : '&nbsp;';
		$t_class = util_alternate_class( $i++ );
		$t_access_level = enum_get_element( 'access_levels', $v_access_level );
		echo "<tr class=\"$t_class\"><td><a href=\"$g_admin_manage_users_edit?f_user_id=$v_id\">$v_username</a></td><td>$v_email</td><td>$t_access_level</td><td>$v_enabled</td><td>$v_protected</td></tr>";
	}
	echo '</tbody></table>';
	echo '<div class="spacer"></div>';
	echo link_create( $g_admin_manage_users_add_page, 'Add User' );

	# @@@ LOCALIZE

	print_footer( __FILE__ );
	print_bottom_page( $g_bottom_page_inc );
	print_body_bottom();
	print_html_bottom();
?>