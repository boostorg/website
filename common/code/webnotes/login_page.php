<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	require_once( 'core' . DIRECTORY_SEPARATOR . 'api.php' );

	### Check to see if already logged in
	if ( ( isset( $g_string_cookie_val ) ) && ( !empty( $g_string_cookie_val ) ) ) {
		login_cookie_check( $g_admin_page );
	}

	$f_msg = gpc_get_string( 'f_msg', '' );

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_meta_inc( $g_meta_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	if ( $f_msg === 'error' ) {
		echo <<<EOT
		<div class="error" align="center">
			<strong>ERROR:</strong> Unauthorised access for supplied user name and password.
		</div>
EOT;
	}

	# Warning, if plain passwords are selected
	if ( config_get( 'auth_type' ) == AUTH_PLAIN ) {
		echo <<<EOT
		<div class="warning" align="center">
			<strong>WARNING:</strong> Plain password authentication is used, this will expose your passwords to administrators.
		</div>
EOT;
	}

	# Generate a warning if administrator/root is valid.
	if ( access_verify_login( 'administrator', 'root' ) ) {
		echo <<<EOT
		<div class="warning" align="center">
			<strong>WARNING:</strong> You should disable the "administrator" account or change its password.
		</div>
EOT;
	}

	echo <<<EOT
	<div class="center">
	<div class="small-width">
		<form name="f_login_form" method="post" action="$g_login">
			<table class="box" summary="">
				<tr class="title">
					<td width="25%"><strong>$s_login_title</strong></td>
					<td width="75%" align="right">[ <a href="signup_page.php"><strong>Sign Up</strong></a> ]</td>
				</tr>
				<tr class="row-1">
					<th>$s_username:</th>
					<td><input type="text" name="f_username" size="32" maxlength="32" /></td>
				</tr>
				<tr class="row-2">
					<th>$s_password:</th>
					<td><input type="password" name="f_password" size="32" maxlength="32" /></td>
				</tr>
				<tr class="row-1">
					<th>$s_save_login:</th>
					<td><input type="checkbox" name="f_perm_login" /></td>
				</tr>
				<tr class="buttons">
					<td colspan="2"><input type="submit" value="$s_login_button" /></td>
				</tr>
			</table>
		</form>
	</div>
	</div>
	
<script type="text/javascript" language="JavaScript">
window.document.f_login_form.f_username.focus();
</script>

EOT;

	print_bottom_page( $g_bottom_page_inc );
	print_footer(__FILE__);
	print_body_bottom();
	print_html_bottom();
?>
