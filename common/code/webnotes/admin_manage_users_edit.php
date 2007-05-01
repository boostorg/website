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

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	print_admin_menu();

	$f_user_id = gpc_get( 'f_user_id' );
	$t_user_array = user_get_row( $f_user_id  );
	extract( $t_user_array, EXTR_PREFIX_ALL, 'v' );

	# @@@ Need to LOCALIZE text
?>
<div align="center">
<div class="small-width">
<form method="post" action="<?php echo $g_admin_manage_users_update ?>">
<input type="hidden" name="f_user_id" value="<?php echo $v_id ?>" />
<table class="box" summary="">
<tr class="title">
	<td colspan="2">
		Update User
	</td>
</tr>
<tr class="row-1">
	<th>
		Username
	</th>
	<td>
		<?php echo $v_username ?>
	</td>
</tr>
<tr class="row-2">
	<th>
		Email
	</th>
	<td>
		<input type="text" name="f_email" value="<?php echo $v_email ?>" />
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
			<?php html_option_list_access_level( $v_access_level ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<th>
		Enabled
	</th>
	<td>
		<input type="checkbox" name="f_enabled" <?php if ( 1 == $v_enabled ) echo 'checked="checked"' ?> />
	</td>
</tr>
<tr class="row-1">
	<th>
		Protected
	</th>
	<td>
		<input type="checkbox" name="f_protected" <?php if ( 1 == $v_protected ) echo 'checked="checked"' ?> />
	</td>
</tr>
<tr class="buttons">
	<td colspan="2">
		<input type="submit" value="Update User" />
		<?php # @@@ LOCALIZE ?>
	</td>
</tr>
</table>
</form>
<div class="spacer"></div>
		<form method="post" action="<?php echo $g_admin_manage_users_delete_page ?>">
			<input type="hidden" name="f_user_id" value="<?php echo $f_user_id ?>" />
			<input type="submit" value="Delete User" />
		</form>

</div>
<?php
	print_footer( __FILE__ );
	print_bottom_page( $g_bottom_page_inc );
	print_body_bottom();
	print_html_bottom();
?>