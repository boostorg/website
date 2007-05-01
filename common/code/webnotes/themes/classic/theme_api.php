<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	require_once ( dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 
					'core' . DIRECTORY_SEPARATOR . 'api.php' );

	# Identifies the version of the theme.  This will allow the phpWebNotes
	# engine in the future to support themes that are designed for older 
	# versions of phpWebNotes.
	function theme_version() {
		return (1);
	}

	# This function is called before printing any notes to the page.
	function theme_notes_start( $p_page, $p_url ) {
		global $g_table_border_color, $g_header_color, $g_white_color,
				$s_user_notes;

		echo <<<EOT
		<div align="center">
			<table bgcolor="$g_table_border_color" width="640" cellspacing="1" border="0" cellpadding="3">
				<tr bgcolor="$g_header_color">
					<td align="center">
						<strong>$s_user_notes</strong>
					</td>
				</tr>
				<tr bgcolor="$g_white_color" height="2">
					<td></td>
				</tr>
EOT;
	}

	# This function is called for every note.  The note information
	# are all included in the associative array that is passed to the
	# function.  The theme should check that a field is defined in 
	# the array before using it.
	function theme_notes_echo( $p_page, $p_url, $p_note_info_array ) {
		global $g_primary_dark_color, $g_primary_light_color, $g_white_color;

		if ( isset( $p_note_info_array['email'] ) ) {
		    $t_email = $p_note_info_array['email'];
		} else {
			$t_email = '';
		}

		if ( isset( $p_note_info_array['date'] ) ) {
		    $t_date = $p_note_info_array['date'];
		} else {
			$t_date = '';
		}

		if ( isset( $p_note_info_array['note'] ) ) {
		    $t_note = '<pre>' . $p_note_info_array['note'] . '</pre>';
		} else {
			$t_note = '&nbsp;';
		}

		echo <<<EOT
		<tr bgcolor="$g_primary_dark_color">
			<td>&nbsp;<em><a href="mailto:$t_email">$t_email</a></em> - $t_date</td>
		</tr>
		<tr bgcolor="$g_primary_light_color">
			<td>$t_note</td>
		</tr>
		<tr bgcolor="$g_white_color" height="2">
			<td></td>
		</tr>

EOT;
	}

	# This function is called after all notes are echo'ed.
	function theme_notes_end( $p_page, $p_url ) {
		global $g_primary_dark_color, $g_note_add_page, $g_admin_manage_notes, $g_admin_page,
				$s_add_note_link, $s_manage, $s_admin;

		$c_url = urlencode( $p_page );
		$t_page_id = page_get_id( $p_page );

		echo <<<EOT
				<tr bgcolor="$g_primary_dark_color">
					<td align="right">
						<a href="$g_note_add_page?f_page_id=$t_page_id&amp;f_url=$c_url">$s_add_note_link</a>
EOT;

		if ( is_moderator() ) {
			echo <<<EOT
				| <a href="$g_admin_manage_notes?f_page_id=$t_page_id&amp;f_url=$c_url">$s_manage</a>
				| <a href="$g_admin_page">$s_admin</a>
EOT;
		}

		echo <<<EOT
					</td>
				</tr>
			</table>
		</div>
EOT;
	}

	# This function is called if the current page has no notes associated
	# with it.  In this case theme_notes_start() and theme_notes_end() 
	# APIs are not called.
	function theme_notes_none( $p_page, $p_url ) {
		theme_notes_start( $p_page, $p_url );
		theme_notes_end( $p_page, $p_url );
	}

	# This function is called if the current page was not indexed
	function theme_not_indexed( $p_page ) {
		global $g_administrator_email, $s_not_indexed_part1, $s_administrator, $s_not_indexed_part2;

		echo <<<EOT
		<div>
			$s_not_indexed_part1 <a href="mailto:$g_administrator_email">$s_administrator</a> $s_not_indexed_part2
		</div>
EOT;
	}
?>
