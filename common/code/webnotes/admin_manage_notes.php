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

	access_ensure_check_action( ACTION_NOTES_MODERATE );

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	print_admin_menu ();

	$query = "SELECT p.*, COUNT(n.id) as notes_count
			FROM " . config_get( 'phpWN_page_table' ) . " p,
				" . config_get( 'phpWN_note_table' ) . " n
			WHERE (p.id=n.page_id) AND (n.visible=".NOTE_VISIBLE_PENDING.")
			GROUP BY p.id 
			ORDER BY notes_count DESC";

	$result = db_query($query);

	echo <<<EOT
	<p><strong>Following are the pages that have notes pending approval:</strong></p>
	<table class="box" summary="">
		<tr><th>Page</th><th>URL</th><th>Pending Notes</th></tr>\n
EOT;
	$count = 0;
	while ( $row = db_fetch_array( $result ) ) {
		$color =  util_alternate_colors( $count++ );
		extract( $row, EXTR_PREFIX_ALL, 'v' );
		echo "<tr bgcolor=\"$color\"><td><a href=\"$v_url\">$v_page</a></td><td>$v_url</td><td>$v_notes_count</td></tr>\n";
	}

	$t_now = date( config_get( 'date_format' ) );
	echo <<<EOT
	</table>
	<p>There are $count page(s) to be moderated.</p>\n
	<hr>
	<div class="spacer"></div>
	<p><strong>Following are all the pages that use phpWebNotes:</strong></p>
	<p>Time now is $t_now</p>
	<table class="box" summary="">
		<tr><th>Page</th><th># of Notes</th><th># of hits</th><th>Last Updated</td><th>URL</th></tr>\n
EOT;

	$count = 0;
	$t_total_visits = 0;
	$t_total_notes = 0;
	$pages = page_get_array ( page_where_url_exists(), 'last_updated DESC' );
	foreach( $pages as $page ) {
		extract( $page, EXTR_PREFIX_ALL, 'v' );
		$t_number = page_notes_count( $v_id );
		$t_total_notes += $t_number;
		$t_visits = page_visits_count( $v_id );
		$t_total_visits += $t_visits;
                $t_last_updated = date( config_get( 'date_format' ), $v_last_updated );

		$color =  util_alternate_colors( $count++ );
		echo "<tr bgcolor=\"$color\"><td><a href=\"$v_url\">$v_page</a></td><td>$t_number</td><td>$t_visits</td><td>$t_last_updated</td><td>$v_url</td></tr>\n";
	}

	echo <<<EOT
	</table>
	<p>There are $count page(s) that are indexed, with $t_total_notes note(s) and $t_total_visits visit(s).</p>\n
EOT;

	print_footer( __FILE__ );
	print_bottom_page( $g_bottom_page_inc );
	print_body_bottom();
	print_html_bottom();
?>
