 <?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	require_once( 'core' . DIRECTORY_SEPARATOR . 'api.php' );

	access_ensure_check_action( ACTION_NOTES_SUBMIT );

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	$f_note_id = gpc_get_int( 'f_note_id', 0 );
	if ( 0 == $f_note_id ) {
		$f_page_id = gpc_get_int( 'f_page_id' );
		$t_default_email = '';
		if ( ( ON == config_get('auto_set_email') ) && access_is_logged_in() ) {
			$t_user_info = user_get_info( user_where_current() );
			if ( false !== $t_user_info ) {
				$t_default_email = $t_user_info['email'];
			}
		}
		$t_default_body = '';
		$t_note_id = 0;
	} else {
	    $t_note_info = note_get_info( note_where_id_equals( $f_note_id ) );
		if ( false === $t_note_info ) {
			# @@@@ proper error
			echo "no note with the specified id";
		    exit;
		}
		$t_default_email = $t_note_info['email'];
		$t_default_body = $t_note_info['note'];
		$t_note_id = db_prepare_int( $f_note_id );
		$f_page_id = $t_note_info['page_id'];
	}
	
	$t_page_name = page_get_name( $f_page_id );
	if ( empty ( $t_page_name ) ) {
		echo "<div align=\"center\">";
		# @@@@ replace with one parameterised localisation string
		echo "$s_not_indexed_part1 <a href=\"mailto:$g_administrator_email\">$s_administrator</a> $s_not_indexed_part2";
		echo "</div>";
	}
	else {
?>

<?php
	# @@@@ When themes are supported, this won't be needed (or at least done in another way.
	### Display a nice message
	if ( file_exists( $g_note_add_include ) ) {
		include( $g_note_add_include );
	}

	$t_base_page_name = basename( $t_page_name );
	#$t_date = date( $g_date_format, time() ); #Remon
	#$t_date = date ('M-d-Y H:i');
	$t_date = date ($g_date_format);
	
	echo <<<EOT
	<div class="center">
	<div class="medium-width">
		<table class="box" summary="">
			<form method="post" action="$g_note_preview_page">
				<input type="hidden" name="f_page_id" value="$f_page_id" />
				<input type="hidden" name="f_note_id" value="$t_note_id" />

				<tr class="form-title">
					<td colspan="2"><strong>$s_add_note</strong></td>
				</tr>

				<tr class="row-1">
					<th width="15%">$s_page</th>
					<td width="85%">$t_base_page_name</td>
				</tr>
				<tr class="row-2">
					<th>$s_date</th>
					<td>$t_date</td>
				</tr>
				<tr class="row-1">
					<th>$s_email</th>
					<td><input type="text" name="f_email" size="64" maxlength="128" value="$t_default_email"/></td>
				</tr>
				<tr class="row-2">
					<th>$s_note</th>
					<td><textarea type="text" name="f_note" rows="16" cols="72">$t_default_body</textarea></td>
				</tr>
				<tr class="form-buttons">
					<td colspan="2" align="center" width="80%"><input type="submit" value="Preview" /></td>
				</tr>
			</form>
		</table>
	</div>
	</div>
EOT;
	} ### end else
	print_footer( __FILE__ );
	print_bottom_page( $g_bottom_page_inc );
	print_body_bottom();
	print_html_bottom();
?>