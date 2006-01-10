<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	###########################################################################
	# Utilities API
	###########################################################################

	### --------------------
	function sql_to_unix_time( $p_timeString ) {
		return mktime( substr( $p_timeString, 8, 2 ),
					   substr( $p_timeString, 10, 2 ),
					   substr( $p_timeString, 12, 2 ),
					   substr( $p_timeString, 4, 2 ),
					   substr( $p_timeString, 6, 2 ),
					   substr( $p_timeString, 0, 4 ) );
	}
	# --------------------
	# alternate color function
	function util_alternate_colors( $p_num, $p_color1='', $p_color2='' ) {
		if ( empty( $p_color1 ) ) {
			$p_color1 = config_get( 'primary_dark_color' );
		}
		if ( empty( $p_color2 ) ) {
			$p_color2 = config_get( 'primary_light_color' );
		}

		if ( 1 == $p_num % 2 ) {
			return $p_color1;
		} else {
			return $p_color2;
		}
	}
	# --------------------
	# alternate color function
	function util_alternate_class( $p_num, $p_class1 = null, $p_class2 = null ) {
		if ( null === $p_class1 ) {
			$p_class1 = 'row-1';
		}
		if ( null === $p_class2 ) {
			$p_class2 = 'row-2';
		}

		if ( 1 == $p_num % 2 ) {
			return $p_class1;
		} else {
			return $p_class2;
		}
	}
	# --------------------
	function util_header_redirect( $p_url ) {
		$t_use_iis = config_get( 'use_iis');
		if ( OFF == $t_use_iis ) {
			@header( 'Status: 302' );
		}

		@header( 'Content-Type: text/html' );
		@header( 'Pragma: no-cache' );
		@header( 'Expires: Fri, 01 Jan 1999 00:00:00 GMT' );
		@header( 'Cache-control: no-cache, no-cache="Set-Cookie", private' );
		if ( ON == $t_use_iis ) {
			@header( "Refresh: 0;url=$p_url" );
		} else {
			@header( "Location: $p_url" );
		}
		die; # additional output can cause problems so let's just stop output here
	}
	### --------------------
	# If $p_var and $p_val are euqal to each other then we echo SELECTED
	# This is used when we want to know if a variable indicated a certain
	# option element is selected
	function check_selected( $p_var, $p_val ) {
		if ( $p_var == $p_val ) {
			echo ' selected="selected" ';
		}
	}
	### --------------------
?>