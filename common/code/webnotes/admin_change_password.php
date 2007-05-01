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

	$row = user_get_info( user_where_current() );
	extract( $row, EXTR_PREFIX_ALL, 'v' );

	if ( 1 == $v_protected ) {
	    $t_action = ACTION_USERS_EDIT_OWN_PROTECTED;
	} else {
	    $t_action = ACTION_USERS_EDIT_OWN;
	}

	access_ensure_check_action( $t_action );

	if ( isset( $f_action ) && ( $f_action == 'change' ) ) {
		$f_current_password = gpc_get_string( 'f_current_password' );
		$f_password = gpc_get_string( 'f_password' );
		$f_password2 = gpc_get_string( 'f_password' );

		if ( false !== user_change_password( user_where_current(), $f_current_password, $f_password, $f_password2 ) ) {
			echo <<<EOT
			<div align="center">
				<p>Password changed successfully</p>
			</div>
EOT;
		}
	}

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	print_admin_menu();

	if ( isset( $pass_change ) && ( $pass_change == 1 ) ) {
		echo '<div align="center">Password changed.</div>';
	} else {
	echo <<<EOT
	<div align="center">
		<form method="post" action="$g_admin_change_password">
			<input type="hidden" name="f_action" value="change" />
			<input type="hidden" name="f_id" value="$v_id" />
			<table class="box">
				<tr class="title">
					<td colspan="2">
						<strong>$s_change_password_title</strong>
					</td>
				</tr>
				<tr class="row-1">
					<th width="25%">$s_username:</th>
					<td width="75%">$v_username</td>
				</tr>
				<tr class="row-2">
					<th>Current Password:</th>
					<td><input type="password" name="f_current_password" size="32" maxlength="32" /></td>
				</tr>
				<tr class="row-1">
					<th>$s_password:</th>
					<td><input type="password" name="f_password" size="32" maxlength="32" /></td>
				</tr>
				<tr class="row-2">
					<th>$s_verify_password:</th>
					<td><input type="password" name="f_password2" size="32" maxlength="32" /></td>
				</tr>
				<tr class="buttons">
					<td colspan="2"><input type="submit" value="$s_change_password_link" /></td>
				</tr>
			</table>
		</form>
	</div>
EOT;
	}

	print_bottom_page( $g_bottom_page_inc );
	print_footer( __FILE__ );
	print_body_bottom();
	print_html_bottom();
?>