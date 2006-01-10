<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	$page_title = 'XHTML';
	$page = 'XHTML';
	$page_parent = 'Markup Languages';
	$page_prev = 'Markup Languages';
	$page_next = 'XML';

	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'sample_header.php' );
?>
<h1>XHTML</h1>
<p>Short for Extensible Hypertext Markup Language, a hybrid between HTML and XML specifically designed for Net device displays.</p>
<p>XHTML is a markup language written in XML; therefore, it is an XML application.</p>
<p>XHTML uses three XML namespaces (used to qualify element and attributes names by associating them with namespaces identified by URI references. Namespaces prevent identically custom-named tags that may be used in different XML documents from being read the same way), which correspond to three HTML 4.0 DTDs: Strict, Transitional, and Frameset.</p>
<p>XHTML markup must conform to the markup standards defined in a HTML DTD.</p>
<p>When applied to Net devices, XHTML must go through a modularization process. This enables XHTML pages to be read by many different platforms.</p>
<p>A device designer, using standard building blocks, will specify which elements are supported. Content creators will then target these building blocks--or modules.</p>
<p>Because these modules conform to certain standards, XHTML's extensibility ensures that layout and presentation stay true-to-form over any platform.</p>
<?php
	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'sample_footer.php' );
?>
