<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	### --------------------
	function pwn_head() {
		print_meta_inc( config_get( 'meta_inc_file' ) );
		print_css( config_get( 'css_inc_file' ) );
		theme_head();
	}
	### --------------------
	function pwn_body( $p_page, $p_url, $p_prev_page = null, $p_next_page = null, $p_parent_page = null ) {
		$t_page_id = page_get_id( $p_page );
		if ( !page_valid_id( $t_page_id ) ) {
			if ( ON === config_get( 'auto_index_pages' ) ) {
				if ( page_add( $p_page ) ) {
					pwn_body( $p_page, $p_url, $p_prev_page, $p_next_page, $p_parent_page );
					return;
				}
			}

			theme_body ( false );
		} else {
			page_update_url( $t_page_id, $p_url );
			page_update_neighbours( $t_page_id, $p_prev_page, $p_next_page, $p_parent_page );
			$page_data = page_prepare_theme_data( $t_page_id );
			theme_body ( $page_data );
			page_visit( $t_page_id );
		}
	}
	### --------------------
	function pwn_page_get_link( $p_page ) {
		return ( page_get_info( page_where_page_equals( $p_page ), 'url' ) );
	}
	### --------------------
	function pwn_page_get_siblings_array( $p_page ) {
		$t_page_info = page_get_info( page_where_page_equals( $p_page ) );
		if ( false === $t_page_info ) {
			return array();
		}

		$t_parent_id = $t_page_info['parent_id'];
		if ( 0 == $t_parent_id ) {
			return array();
		}

		$t_id = $t_page_info['id'];

		$query = "SELECT page
				FROM " . config_get( 'phpWN_page_table' ) . "
				WHERE ( parent_id = $t_parent_id )
				ORDER BY page ASC";
		$result = db_query( $query );

		$t_pages_array = array();
		while ( $row = db_fetch_array( $result ) ) {
			$t_pages_array[] = $row['page'];
		}

		return $t_pages_array;
	}
	### --------------------
	function pwn_index( $p_page, $p_page_top, $p_page_parent ) {
		$images = config_get( 'web_directory' ) . 'themes/' . config_get( 'theme' ) . '/images/';

		$image_top = $images . 'caret_top.gif';
		$image_up = $images . 'caret_up.gif';
		$image_sibling = $images . 'bullet_sibling.gif';
		$image_current = $images . 'bullet_current.gif';

		if ( $p_page != $p_page_top ) {
			$link = pwn_page_get_link( $p_page_top );
			echo "<a href=\"$link\"><img class=\"bullet\" src=\"$image_top\" alt=\"$p_page_top\" />$p_page_top</a><br />\n";
		}

		if ( ( $p_page_parent != null ) && ( $p_page_parent != $p_page_top ) ) {
			echo "<hr noshade=\"noshade\" />\n";
			$link = pwn_page_get_link( $p_page_parent );
			echo "<a href=\"$link\"><img class=\"bullet\" src=\"$image_up\" alt=\"$p_page_parent\" />$p_page_parent</a><br />";
		}

		$siblings = pwn_page_get_siblings_array( $p_page );
		if ( count( $siblings ) > 0 ) {
			echo "<small>";

			foreach( $siblings as $sibling ) {
				if ( $sibling == $p_page ) {
					$bullet = $image_current;
				} else {
					$bullet = $image_sibling;
				}
				$link = pwn_page_get_link( $sibling );
				echo "<a href=\"$link\"><img class=\"bullet\" src=\"$bullet\" alt=\"$sibling\" />$sibling</a><br />";
			}

			echo "</small>";
		}
	}
?>