<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	require_once ( 'core' . DIRECTORY_SEPARATOR . 'api.php' );
	login_cookie_check();

	access_ensure_check_action( ACTION_PAGES_MANAGE );

	if ( !isset( $f_dir ) ) {
		$f_dir = dirname( __FILE__ );
	} else {
		$f_dir = stripslashes( urldecode( $f_dir ) );
	}

	if ( ( substr( $f_dir, -1 ) != '\\' ) && ( substr ( $f_dir, -1 ) != '/' ) ) {
		$f_dir = $f_dir . DIRECTORY_SEPARATOR;
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
?>
<div align="center">
<table bgcolor="<?php echo $g_table_border_color ?>" width="75%" cellspacing="1" border="0">
<tr bgcolor="<?php echo $g_header_color ?>">
	<td>
		<strong><?php echo $s_index_files_title ?></strong>
	</td>
</tr>
<tr bgcolor="<?php echo $g_white_color ?>">
	<td>
		<?php
			echo "$s_current_directory: $f_dir";
		?>
	</td>
</tr>
<?php
	if ( isset( $f_action_index ) ) {
		page_add_dir( $f_dir, false );
	}

	if ( isset( $f_action_index_tree ) ) {
		page_add_dir( $f_dir, true );
	}
?>
<tr bgcolor="<?php echo $g_primary_light_color ?>">
	<td>
		<?php
			print_dirs( $f_dir, $PHP_SELF );
		?>
	</td>
</tr>
<tr bgcolor="<? echo $g_white_color ?>" align="center">
	<form method="post" action="<? echo $g_admin_index_files ?>">
	<input type="hidden" name="f_dir" value="<? echo $f_dir?>" />
	<td>
		<? echo $s_index_msg ?>
		<br />
		<input type="submit" name="f_action_index" value="Index Current Dir" />
		<input type="submit" name="f_action_index_tree" value="<? echo $s_index_files_link ?>" />
	</td>
	</form>
</tr>
</table>
</div>

<?php
	print_footer( __FILE__ );
	print_bottom_page( $g_bottom_page_inc );
	print_body_bottom();
	print_html_bottom();
?>