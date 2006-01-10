<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	# prevent caching
	global $g_content_expire;

	if ( !isset( $g_content_expire ) ) {
		$g_content_expire = 0;
	}
?>
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-cache" />
<meta http-equiv="Pragma-directive" content="no-cache" />
<meta http-equiv="Cache-Directive" content="no-cache" />
<meta http-equiv="Expires" content="<?php echo $g_content_expire ?>" />