<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	#########################################################################
	# This is an empty template to be used to create new themes.
	# To create a new theme, please follow the following steps:
	#   - Create a directory under the themes directory with the theme name.
	#   - Make a copy of this file under the new theme directory
	#   - Assign $g_theme to the theme name (not the full path) in 
	#     core/custom_config_inc.php 
	#########################################################################

	# The path to the api.php is calculated assume this file resides in a sub-directory
	# under themes.  For example, themes/phpnet/.
	require_once ( dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 
					'core' . DIRECTORY_SEPARATOR . 'api.php' );

	### --------------------
	function theme_head() {
		global $g_web_directory;
		$t_style = $g_web_directory . 'themes/phpnet/theme.css';
		print_css_link( $t_style );
	}
	### --------------------
	function theme_body( $p_page_data ) {
		if ( false === $p_page_data ) {
			# @@@ Handle not indexed (and auto index off)
			return;
		}

		global $g_note_add_page, $s_add_note_link, $s_manage, $s_admin, $g_web_directory,
				$g_theme;

		$t_notes = $p_page_data['notes'];
		$t_page = $p_page_data['page'];

		$t_page_id = $p_page_data['id'];

		$t_images_base = $g_web_directory . 'themes/' . $g_theme . '/images/';
		$add_picture = $t_images_base . 'notes_add.gif';
		$help_picture = $t_images_base . 'notes_about.gif';
		$prev_picture = $t_images_base . 'caret_left.gif';
		$next_picture = $t_images_base . 'caret_right.gif';

		if ( false === $p_page_data['preview'] ) {
			$t_link_start = "<a href=\"$g_note_add_page?f_page_id=$t_page_id\">";
			$t_link_end = '</a>';
		} else {
			$t_link_start = $t_link_end = '';
		}

		#
		# HEADER
		#

		$t_about_page = config_get( 'about_page' );

		echo <<<EOT
		<div class="phpnet">
		<table summary="" cellpadding="4" cellspacing="0">
			<tr class="dark">
				<td><small>User Contributed Notes</small><br /><strong>$t_page</strong></td>
				<td align="right">
					$t_link_start<img src="$add_picture" width="13" height="13" alt="Add Notes" />$t_link_end
					<a href="$t_about_page"><img src="$help_picture" width="13" height="13" alt="About Notes" border="0" />
				</td>
			</tr>
EOT;

		#
		# NOTES
		#

		if ( 0 === count( $t_notes ) ) {
			echo <<<EOT
			<tr class="light">
				<td colspan="2">There are no user contributed notes for this page.</td>
			</tr>
EOT;
		} else {
			for ( $i = 0; $i < count( $t_notes ); $i++ ) {
				$t_moderation = '';
				$t_note_info = $t_notes[$i];

				if ( false === $p_page_data['preview'] ) {
					if ( access_check_action( ACTION_NOTES_MODERATE ) ) {
						$t_url = $p_page_data['url'];
						$t_moderation = '';

						if ( $t_note_info['visible'] != NOTE_VISIBLE_ACCEPTED ) {
							$t_moderation .= link_note_action( $t_note_info['id'], 'accept', $t_url, 
								access_check_action( ACTION_NOTES_MODERATE_ACCEPT ) ) . ' ';
						}

						if ( $t_note_info['visible'] != NOTE_VISIBLE_PENDING ) {
							$t_moderation .= link_note_action( $t_note_info['id'], 'queue', $t_url, 
								access_check_action( ACTION_NOTES_MODERATE_QUEUE ) ) . ' ';
						}

						if ( $t_note_info['visible'] != NOTE_VISIBLE_DECLINED ) {
							$t_moderation .= link_note_action( $t_note_info['id'], 'decline', $t_url, 
								access_check_action( ACTION_NOTES_MODERATE_DECLINE ) ) . ' ';
						}

						if ( $t_note_info['visible'] != NOTE_VISIBLE_ARCHIVED ) {
							$t_moderation .= link_note_action( $t_note_info['id'], 'archive', $t_url, 
								access_check_action( ACTION_NOTES_MODERATE_ARCHIVE ) ) . ' ';
						}

						$t_moderation .= link_note_action( $t_note_info['id'], 'edit', $t_url, 
							access_check_action( ACTION_NOTES_EDIT ) );

						if ( $t_note_info['visible'] != NOTE_VISIBLE_DELETED ) {
							$t_moderation .= link_note_action( $t_note_info['id'], 'delete', $t_url, 
								access_check_action( ACTION_NOTES_MODERATE_DELETE ) );
						}
					}
				}

				if ( isset( $t_note_info['id'] ) && ( $t_note_info['id'] != 0 ) ) {
					$t_id = (integer)$t_note_info['id'];
					$t_visibility = '';
					if ( NOTE_VISIBLE_ACCEPTED != $t_note_info['visible'] ) {
						$t_visibility = '(' . note_get_visibility_str( $t_note_info['visible'] ) . ') - ';
					}
					$t_id_view = "<tt>$t_visibility#$t_id<br />$t_moderation</tt>";
					$t_id_bookmark = "<a name=\"$t_id\"></a>";
				} else {
					$t_id_view = '&nbsp;';
					$t_id_bookmark = '';
				}

				if ( isset( $t_note_info['email'] ) ) {
					$t_email = $t_note_info['email'];
				} else {
					$t_email = '';
				}

				if ( isset( $t_note_info['date'] ) ) {
					# 06-Feb-2002 02:28
					$t_date = date('d-M-Y G:i', $t_note_info['date']);
				} else {
					$t_date = '';
				}

				if ( isset( $t_note_info['note'] ) ) {
					$t_note = nl2br('<tt>' . $t_note_info['note'] . '</tt>');
				} else {
					$t_note = '&nbsp;';
				}

				echo <<<EOT
				<tr class="light">
					<td colspan="2">
						$t_id_bookmark

						<table summary="" cellpadding="2" cellspacing="0">
							<tr class="light">
								<td><strong>$t_email</strong><br />$t_date</td>
								<td align="right">$t_id_view</td>
							</tr>
							<tr class="lighter">
								<td colspan="2">$t_note</td>
							</tr>
						</table>
					</td>
				</tr>
EOT;
			}
		}

		#
		# FOOTER
		#

		if ( empty( $p_page_data['prev_page'] ) ) {
			$t_prev_text = '';
		} else {
			$t_prev_text = "<img src=\"$prev_picture\" width=\"11\" height=\"7\" alt=\"" . $p_page_data['prev_page'] . "\" />" .
			link_create( $p_page_data['prev_url'], $p_page_data['prev_page'], true, '', '' );
		}

		if ( empty( $p_page_data['next_page' ] ) ) {
			$t_next_text = '';
		} else {
			$t_next_text = link_create( $p_page_data['next_url'], $p_page_data['next_page'], true, '', '' ) .
			"<img src=\"$next_picture\" width=\"11\" height=\"7\" alt=\"" . $p_page_data['next_page'] . "\" />";
		}

		if ( empty( $t_prev_text ) && empty( $t_next_text ) ) {
			$t_navigation_row = '';
		} else {
			$t_navigation_row = "<tr><td>$t_prev_text</td><td align=\"right\">$t_next_text</td></tr>";
		}

		if ( false === $p_page_data['preview'] ) {
			$t_link_start = "<a href=\"$g_note_add_page?f_page_id=$t_page_id\">";
			$t_link_end = '</a>';
		} else {
			$t_link_start = $t_link_end = '';
		}

		if ( 0 !== count( $t_notes ) ) {
			echo <<<EOT
			<tr class="dark">
				<td colspan="2" align="right">
				$t_link_start<img src="$add_picture" width="13" height="13" alt="Add Notes" />$t_link_end
				<img src="$help_picture" width="13" height="13" alt="About Notes" />
				</td>
			</tr>
EOT;
		}

		if ( false === $p_page_data['preview'] ) {
			# Tue, 17 Sep 2002
			$t_last_updated = date('D, d M Y - G:i:s', $p_page_data['last_updated']);
			echo <<<EOT
			<tr class="dark"><td colspan="2">
				<table class="light" cellpadding="0" cellspacing="4">
					$t_navigation_row
					<tr><td align="right" colspan="2"><small>Last updated: $t_last_updated</small></td></tr>
				</table>
			</td></tr>
EOT;
		}

		echo '</table></div>';

		if ( ( false === $p_page_data['preview'] ) && ( access_is_logged_in() ) ) {
			echo '<div class="pwn">';
			print_admin_menu();
			echo '</div>';
		}
	}
	### --------------------
?>
