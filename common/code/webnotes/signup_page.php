<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	require_once( 'core' . DIRECTORY_SEPARATOR . 'api.php' );

	if ( OFF == config_get( 'allow_signup' ) ) {
		util_header_redirect( $g_login_page );
	}

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_meta_inc( $g_meta_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	if ( isset( $submit ) ) {				
		$t_username = gpc_get_string( 'f_username' );
		$t_email = gpc_get_string( 'f_email' );

		if ( user_signup( $t_username, $t_email ) ) {
			echo <<<EOT
				<div align="center">
					<p>An e-mail is sent to <a href="mailto:$t_email">$t_email</a> with the login details. It is recommended to change your password on first login.</p>
					[ <a href="$g_login_page"><strong>Login</strong></a> ]
				</div>
EOT;
		} else {
			# @@@@ proper error
			echo "Unable to signup user.<br />";
		}

		print_bottom_page( $g_bottom_page_inc );
		print_footer( __FILE__ );
		print_body_bottom();
		print_html_bottom();
		exit;		
	}

	echo <<<EOT
	<div class="spacer"></div>
	<div align="center">
	<div class="small-width">
		<form name="f_signup_form" action="$PHP_SELF" method="post">
			<table class="box" summary="">
				<tr class="title">
					<td colspan="2"><strong>Sign Up</strong></td>
				</tr>
				<tr class="row-1">
					<th width="25%">$s_username:</th>
					<td width="75%"><input type="text" name="f_username" size="32" maxlength="32" /></td>
				</tr>
				<tr class="row-2">
					<th>E-mail:</th>
					<td><input type="text" name="f_email" size="32" maxlength="64" /></td>
				</tr>
				<tr class="buttons">
					<td colspan="2"><input type="submit" name="submit" value="Register" /></td>
				</tr>
			</table>
		</form>
	</div>
	</div>
	
<script type="text/javascript" language="JavaScript">
window.document.f_signup_form.f_username.focus();
</script>

EOT;

	print_bottom_page( $g_bottom_page_inc );
	print_footer( __FILE__ );
	print_body_bottom();
	print_html_bottom();
?>