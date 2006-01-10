<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	$page_title = 'Web Services';
	$page = 'Web Services';
	$page_parent = 'Webopedia';
	$page_prev = 'XHTML';
	$page_next = null;

	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'sample_header.php' );
?>
		<h1>Web services</h1>
  
		<p>The term Web services describes a standardized way of integrating Web-based applications using the XML, SOAP, WSDL and UDDI open standards over an Internet protocol backbone. XML is used to tag the data, SOAP is used to transfer the data, WSDL is used for describing the services available and UDDI is used for listing what services are available. Used primarily as a means for businesses to communicate with each other and with clients, Web services allow organizations to communicate data without intimate knowledge of each other's IT systems behind the firewall.</p>
		<p>Unlike traditional client/server models, such as a Web server/Web page system, Web services do not provide the user with a GUI. Web services instead share business logic, data and processes through a programmatic interface across a network. The applications interface, not the users. Developers can then add the Web service to a GUI (such as a Web page or an executable program) to offer specific functionality to users.</p>

		<p>Web services allow different applications from different sources to communicate with each other without time-consuming custom coding, and because all communication is in XML, Web services are not tied to any one operating system or programming language. For example, Java can talk with Perl, Windows applications can talk with UNIX applications.</p>

		<p>Web services do not require the use of browsers or HTML.</p>

		<p>Web services are sometimes called application services.</p>

		<p>The above definition was copied from <a href="http://www.webopedia.com">Webopedia</a>.</p>
<?php
	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'sample_footer.php' );
?>
