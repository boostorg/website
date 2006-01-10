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
	theme_head();

	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_spacer();

	$f_note_id = gpc_get_int( 'f_note_id' );
	$f_page_id = gpc_get_int( 'f_page_id' );
	$f_email = gpc_get_string( 'f_email' );
	$f_note = gpc_get_string( 'f_note' );

	$t_page_info = page_get_info( page_where_id_equals( $f_page_id ) );
	if ( false === $t_page_info ) {
		echo "page not found";
	    exit;
	}

	$t_note['id']	= '0';
	$t_note['email']= string_prepare_note_for_viewing( $f_email );
	$t_note['date']	= time(); 
	$t_note['note']	= string_prepare_note_for_viewing( $f_note );

	$t_page_data = array();
	$t_page_data['id'] = 0;
	$t_page_data['page'] = page_get_name( $f_page_id );
	$t_page_data['url'] = $t_page_info['url'];
	$t_page_data['preview'] = true;
	$t_page_data['prev_page'] = '';
	$t_page_data['prev_url'] = '';
	$t_page_data['next_page'] = '';
	$t_page_data['next_url'] = '';
	$t_page_data['notes'] = array ( $t_note );

	theme_body( $t_page_data );

	$f_email = string_to_form( $f_email );
	$f_note = string_to_form( $f_note );

	echo <<<EOT
	<div class="spacer"></div>
	<form method="post" action="$g_note_add">
		<input type="hidden" name="f_note_id" value="$f_note_id" />
		<input type="hidden" name="f_page_id" value="$f_page_id" />
		<input type="hidden" name="f_email" value="$f_email" />
		<input type="hidden" name="f_note" value="$f_note" />
		<input type="submit" name="f_submit" value="Submit" />
	</form>
EOT;

	print_footer( __FILE__ );
	print_body_bottom();
	print_html_bottom();
?>