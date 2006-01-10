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

	access_ensure_check_action( ACTION_USERS_ADD );

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	print_admin_menu();

	# @@@ Need to LOCALIZE text
?>
<div align="center">
<div class="small-width">
<form method="post" action="<?php echo $g_admin_manage_users_add ?>">
<table class="box" summary="">
<tr class="title">
	<td colspan="2">
		Add User
	</td>
</tr>
<tr class="row-1">
	<th>
		Username
	</th>
	<td>
		<input type="text" name="f_username" value="" />
	</td>
</tr>
<tr class="row-2">
	<th>
		Email
	</th>
	<td>
		<input type="text" name="f_email" value="" />
	</td>
</tr>
<tr class="row-1">
	<th>
		Password
	</th>
	<td>
		<input type="password" name="f_password" value="" />
	</td>
</tr>
<tr class="row-2">
	<th>
		Password Confirm
	</th>
	<td>
		<input type="password" name="f_password_confirm" value="" />
	</td>
</tr>
<tr class="row-1">
	<th>
		Access Level
	</th>
	<td>
		<select name="f_access_level">
			<?php html_option_list_access_level( REGISTERED ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<th>
		Enabled
	</th>
	<td>
		<input type="checkbox" name="f_enabled" checked="checked" />
	</td>
</tr>
<tr class="row-1">
	<th>
		Protected
	</th>
	<td>
		<input type="checkbox" name="f_protected" />
	</td>
</tr>
<tr class="buttons">
	<td colspan="2">
		<input type="submit" value="Add User" />
	</td>
</tr>
</table>
</form>
</div>
</div>
<?php
	print_footer( __FILE__ );
	print_bottom_page( $g_bottom_page_inc );
	print_body_bottom();
	print_html_bottom();
?>