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

	if ( !isset ( $f_action ) ) {
		$f_action = 'none';
	}

	if ( $f_action == 'accept' ) {
		note_accept( $f_id );
	}

	if ( $f_action == 'decline' ) {
		note_decline( $f_id );
	}

	$result = note_queue();
	$row = db_fetch_array( $result );
	if ( $row ) {
		extract( $row, EXTR_PREFIX_ALL, "v" );
		$v_note = string_edit( $v_note );
	}

	$queue_count = note_queue_count();

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	print_admin_menu();

	# @@@@ The HTML below needs cleanup
?>
	<br />
	<table bgcolor="<? echo $g_table_border_color ?>" width="75%" cellspacing="1" border="0">
		<tr bgcolor="<? echo $g_header_color ?>">
			<td colspan="2">
				<strong><? echo $s_view_queue_title ?></strong>
			</td>
		</tr>
		<tr bgcolor="<? echo $g_white_color ?>">
			<td colspan="2" align="right">
				[<strong><? echo $queue_count ?></strong>] <? echo $s_items_in_queue ?>
			</td>
		</tr>
		<? if ( $queue_count > 0 ) { ?>
		<tr bgcolor="<? echo $g_primary_dark_color ?>">
			<td width="15%" align="center">
				<? $s_page ?>
			</td>
			<td width="85%">
				<a href="<? echo string_get_url( $v_page ) ?>"><? echo basename( $v_page ) ?></a>
			</td>
		</tr>
		<tr bgcolor="<? echo $g_primary_light_color ?>">
			<td align="center">
				<? echo $s_date ?>
			</td>
			<td>
				<? echo $v_date_submitted ?>
			</td>
		</tr>
		<tr bgcolor="<? echo $g_primary_dark_color ?>">
			<td align="center">
				<? echo $s_email ?>
			</td>
			<td>
				<? echo $v_email ?>
			</td>
		</tr>
		<tr bgcolor="<? echo $g_primary_light_color ?>">
			<td align=center>
				<? echo $s_ip ?>
			</td>
			<td>
				<? echo $v_ip ?>
			</td>
		</tr>
		<tr bgcolor="<? echo $g_primary_dark_color ?>">
			<td align="center">
				<? echo $s_note ?>
			</td>
			<form method="post" action="<? echo $g_admin_view_queue ?>">
				<input type="hidden" name="f_action" value="accept" />
				<input type="hidden" name="f_id" value="<? echo $v_id ?>" />
				<td>
					<textarea type="text" name="f_note" rows="16" cols="72"><? echo $v_note ?></textarea>
				</td>
		</tr>
		<tr bgcolor="<? echo $g_white_color ?>">
				<td>
				</td>
				<td>
					<table width="100%">
		<tr>
			<td width="50%" align="center">
				<input type="submit" value="<? echo $s_accept_link ?>" />
			</td>
			</form>
			<form method="post" action="<? echo $g_admin_view_queue ?>" />
				<input type="hidden" name="f_action" value="decline" />
				<input type="hidden" name="f_id" value="<? echo $v_id ?>" />
				<td width="50%" align="center">
					<input type="submit" value="<? echo $s_decline_link ?>" />
				</td>
			</form>
		</tr>
	</table>
	</td>
</tr>
<? } ### end if ?>
</table>
</div>

<?php
	print_footer( __FILE__ );
	print_bottom_page( $g_bottom_page_inc );
	print_body_bottom();
	print_html_bottom();
?>