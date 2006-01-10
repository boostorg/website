<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	### --------------------
	function print_html_top() {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' .
			"\n<html>";
	}
	### --------------------
	function print_head_top() {
		echo '<head>';
	}
	### --------------------
	function print_title( $p_title ) {
		echo "<title>$p_title</title>";
	}
	### --------------------
	function print_css( $p_css = '' ) {
		if ( !empty( $p_css ) && file_exists( $p_css ) ) {
			include_once( $p_css );
		}
	}
	### --------------------
	function print_css_link( $p_css ) {
		echo "<link href=\"$p_css\" rel=\"stylesheet\" type=\"text/css\" />";
	}
	### --------------------
	function print_meta_inc( $p_meta_inc = '' ) {
		if ( !empty( $p_meta_inc ) && file_exists( $p_meta_inc ) ) {
			include_once( $p_meta_inc );
		}
	}
	### --------------------
	function print_meta_redirect( $p_url, $p_time ) {
		echo "<meta http-equiv=\"Refresh\" content=\"$p_time;URL=$p_url\">";
	}
	### --------------------
	function print_head_bottom() {
		echo '</head>';
	}
	### --------------------
	function print_body_top() {
		echo '<body><div class="pwn">';
	}
	### --------------------
	function print_header( $p_title = '' ) {
		echo <<<EOT
		<div class="title">
			$p_title
		</div>
EOT;
	}
	### --------------------
	function print_top_page( $p_page ) {
		if ( !empty( $p_page ) && file_exists( $p_page ) ) {
			echo '<div class="top-file">';
			include_once( $p_page );
			echo '</div>';
		}
	}
	### --------------------
	function print_bottom_page( $p_page ) {
		if ( !empty( $p_page ) && file_exists( $p_page ) ) {
			echo '<div class="bottom-file">';
			include_once( $p_page );
			echo '</div>';
		}
	}
	### --------------------
	function print_footer( $p_file ) {
		global 	$g_webmaster_email;

		echo '<div class="footer">';
		print_phpWebNotes_version();
		echo '<span class="copyright">Copyright (c) 2000-2002</span><br />';
		echo "<address><a href=\"mailto:$g_webmaster_email\">$g_webmaster_email</a></address>";
		echo '</div>';
	}
	### --------------------
	function print_body_bottom() {
		echo '</div></body>';
	}
	### --------------------
	function print_html_bottom() {
		echo '</html>';
	}
	### --------------------
	###########################################################################
	# HTML Appearance Helper API
	###########################################################################
	### --------------------
	### checks to see whether we need to be displaying the version number
	function print_phpWebNotes_version() {
		if ( ON == config_get( 'show_version' ) ) {
			echo '<span class="version">phpWebNotes - ' . config_get( 'phpWebNotes_version' ) . '</span><br />';
		}
	}
	### --------------------
	function print_spacer() {
		echo '<div class="spacer"></div>';
	}
	### --------------------
	function print_admin_menu( $p_add_space = true ) {
		global 	$g_logout, $g_admin_index_files, $g_admin_change_password,
			$g_admin_manage_notes, $g_admin_manage_users,
			$s_logout_link, $s_index_files, $s_change_password,
			$s_manage_notes, $s_manage_users, $g_user_home_page;

		$queue_count = note_queue_count();

		echo '<div class="menu">.: ';
		echo "<a title=\"Go to your home page\" href=\"$g_user_home_page\">Home</a> :: ";
		#if ( access_check_action( ACTION_PAGES_MANAGE ) ) {
		#	echo "<a title=\"Add or remove pages\" href=\"$g_admin_index_files\">$s_index_files</a> :: ";
		#}
		if ( access_check_action( ACTION_NOTES_MODERATE ) ) {
			echo "<a title=\"Moderate notes\" href=\"$g_admin_manage_notes\">$s_manage_notes</a> [$queue_count] :: ";
		}
		if ( access_check_action( ACTION_USERS_MANAGE ) ) {
			echo "<a title=\"View/edit user information\" href=\"$g_admin_manage_users\">$s_manage_users</a> :: ";
		}

		$row = user_get_info( user_where_current() );
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		if ( 1 == $v_protected ) {
		    $t_action = ACTION_USERS_EDIT_OWN_PROTECTED;
		} else {
		    $t_action = ACTION_USERS_EDIT_OWN;
		}

		if ( access_check_action( $t_action ) ) {
			echo "<a title=\"Change your own password\" href=\"$g_admin_change_password\">$s_change_password</a> :: ";
		}

echo <<<EOT
		<a title="Logout from phpWebNotes" href="$g_logout">$s_logout_link</a> :.
		</div>
EOT;
	}
	# --------------------
	# print the bracketed links used near the top
	# if the $p_link is blank then the text is printed but no link is created
	function print_bracket_link( $p_link, $p_url_text ) {
		if ( empty( $p_link ) ) {
			echo "[ $p_url_text ]";
		} else {
			echo "[ <a href=\"$p_link\">$p_url_text</a> ]";
		}
	}
	### --------------------
	function html_option_list_access_level( $p_access_level = '' ) {
		$ids = enum_get_ids_array( 'access_levels' );

		foreach ( $ids as $id ) {
			if ( ( NOBODY == $id ) || ( EVERYBODY == $id ) ) {
				continue;
			}

			echo '<option value="' . $id . '" ';
			check_selected( $p_access_level, $id );
			echo '>' . enum_get_element( 'access_levels', $id ) . '</option>';
		}
	}
	### --------------------
?>
