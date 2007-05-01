<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	$page_title = 'Webopedia';
	$page = 'Webopedia';
	$page_parent = null;
	$page_prev = null;
	$page_next = 'Markup Languages';

	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'sample_header.php' );
?>
		<h1>Webopedia</h1>

		<p>This is a sample document that contains multiple pages.  Each page can have its
		own notes.  Pages are linked together through previous/next nagivation, as well as
		the top, parent, and siblings on the left side.</p>

		<p>The contents of the pages lists in this document is copied from <a
		href="http://www.webopedia.com">Webopedia</a> to serve as a sample.</p>

<?php
	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'sample_footer.php' );
?>
