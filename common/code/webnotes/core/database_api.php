<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	###########################################################################
	# Database : MYSQL for now
	###########################################################################
	### --------------------
	# connect to database
	function db_connect($p_hostname="localhost", $p_username="root",
						$p_password="", $p_database="webnotes",
						$p_port=3306 ) {

		$t_result = mysql_connect(  $p_hostname.":".$p_port,
									$p_username, $p_password );
		$t_result = mysql_select_db( $p_database );

		### Temproary error handling
		if ( !$t_result ) {
			echo "ERROR: FAILED CONNECTION TO DATABASE";
			exit;
		}
	}
	### --------------------
	# persistent connect to database
	function db_pconnect($p_hostname="localhost", $p_username="root",
						$p_password="", $p_database="webnotes",
						$p_port=3306 ) {

		$t_result = mysql_pconnect(  $p_hostname.":".$p_port,
									$p_username, $p_password );
		$t_result = mysql_select_db( $p_database );

		### Temproary error handling
		if ( !$t_result ) {
			echo "ERROR: FAILED CONNECTION TO DATABASE";
			exit;
		}
	}
	### --------------------
	# execute query, requires connection to be opened,
	# goes to error page if error occurs
	# Use this when you don't want to handler an error yourself
	function db_query( $p_query ) {

		$t_result = mysql_query( $p_query );
		if ( !$t_result ) {
			echo "ERROR: FAILED QUERY: ".$p_query;
			exit;
		}
		else {
			return $t_result;
		}
	}
	### --------------------
	function db_select_db( $p_db_name ) {
		return mysql_select_db( $p_db_name );
	}
	### --------------------
	function db_num_rows( $p_result ) {
		return mysql_num_rows( $p_result );
	}
	### --------------------
	function db_fetch_array( $p_result ) {
		return mysql_fetch_array( $p_result );
	}
	### --------------------
	function db_result( $p_result, $p_index1=0, $p_index2=0 ) {
		if ( $p_result && ( db_num_rows( $p_result ) > 0 ) ) {
			return mysql_result( $p_result, $p_index1, $p_index2 );
		}
		else {
			return false;
		}
	}
	# --------------------
	# return the last inserted id
	function db_insert_id() {
		if ( mysql_affected_rows() > 0 ) {
			return mysql_insert_id(); 
		} else  {
			return false; 
		}
	}
	### --------------------
	function db_close() {
		$t_result = mysql_close();
	}
	### --------------------
	# --------------------
	# prepare a string before DB insertion
	function db_prepare_string( $p_string ) {
		return mysql_escape_string( $p_string );
	}
	# --------------------
	# prepare an integer before DB insertion
	function db_prepare_int( $p_int ) {
		return (integer)$p_int;
	}
	# --------------------
	# prepare a boolean before DB insertion
	function db_prepare_bool( $p_bool ) {
		return (int)(bool)$p_bool;
	}
	# --------------------
	# generic unprepare if type is unknown
	function db_unprepare( $p_string ) {
		return stripslashes( $p_string );
	}
	# --------------------
	# unprepare a string after taking it out of the DB
	function db_unprepare_string( $p_string ) {
		return db_unprepare( $p_string );
	}
	# --------------------
	# unprepare an integer after taking it out of the DB
	function db_unprepare_int( $p_int ) {
		return (integer)db_unprepare( $p_int );
	}
	# --------------------
	# unprepare a boolean after taking it out of the DB
	function db_unprepare_bool( $p_bool ) {
		return (bool)db_unprepare( $p_bool );
	}
	# --------------------
	# calls db_unprepare() on every item in a row
	function db_unprepare_row( $p_row ) {
		if ( false == $p_row ) {
			return false;
		}

		$t_new_row = array();

		while ( list( $t_key, $t_val ) = each( $p_row ) ) {
			$t_new_row[$t_key] = db_unprepare( $t_val );
		}

		return $t_new_row;
	}

	###########################################################################
	### CODE TO EXECUTE                                                     ###
	###########################################################################

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
?>