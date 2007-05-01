<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	require_once ( 'core' . DIRECTORY_SEPARATOR . 'api.php' );

	print_html_top();
	print_head_top();
	print_title( $g_window_title );
	print_css( $g_css_inc_file );
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_page_inc );

	echo '<p>Fore more information about phpWebnotes visit our website at <a href="http://webnotes.futureware.biz">http://webnotes.futureware.biz</a>.</p>';

	print_footer( __FILE__ );
	print_bottom_page( $g_bottom_page_inc );
	print_body_bottom();
	print_html_bottom();
?>