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

	access_ensure_check_action( ACTION_USERS_DELETE );

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	print_admin_menu();

	$f_user_id = gpc_get_int( 'f_user_id' );

	$t_user_info = user_get_info( user_where_id_equals( $f_user_id ) );
?>
<div align="center">
Are you sure you want to delete user '<?php echo $t_user_info['username'] ?>'?<br />
<div class="spacer"></div>
	<form method="post" action="<?php echo $g_admin_manage_users_delete ?>">
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