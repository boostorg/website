<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	### --------------------
	function page_where_url_exists() {
		return ("(url <> '')");
	}
	### --------------------
	function page_where_id_equals( $p_page_id ) {
		$c_page_id = db_prepare_int( $p_page_id );
		return ("(id=$c_page_id)");
	}
	### --------------------
	function page_where_all() {
		return ("(1=1)");
	}
	### --------------------
	function page_where_page_equals( $p_page ) {
		$c_page = db_prepare_string( $p_page );
		return ("(page='$c_page')");
	}
	### --------------------
	# $p_where is constructed by page_where* and hence does not need to be cleaned.
	function page_get_info ( $p_where, $p_field = null ) {
		$query = "SELECT *, UNIX_TIMESTAMP(last_updated) as last_updated
				FROM " . config_get( 'phpWN_page_table' ) . "
				WHERE $p_where
				LIMIT 1";

		$result = db_query( $query );
		if ( db_num_rows( $result) > 0 ) {
			$t_info = db_fetch_array( $result );

			if ( null === $p_field ) {
				return $t_info;
			} else {
				#echo "$p_field\n"; var_dump($t_info); exit;
				return $t_info["$p_field"];
			}
		}

		return false;
	}
	### --------------------
	# $p_where is constructed by page_where* and hence does not need to be cleaned.
	function page_get_array ( $p_where, $p_order = null ) {
		if ( null !== $p_order ) {
			$c_order = 'ORDER BY ' . db_prepare_string( $p_order );
		} else {
			$c_order = '';
		}

		$query = "SELECT *, UNIX_TIMESTAMP(last_updated) as last_updated
				FROM " . config_get( 'phpWN_page_table' ) . "
				WHERE $p_where
				$c_order";

		$t_array = array();
		$result = db_query( $query );
		while ( $row = db_fetch_array( $result ) ) {
			$t_array[] = $row;
		}

		return $t_array;
	}
	### --------------------
	function page_get_id( $p_page ) {
		return ( page_get_info( page_where_page_equals( $p_page ), 'id' ) );
	}
	### --------------------
	function page_valid_id( $p_page_id ) {
		return ( false !== $p_page_id );
	}
	### --------------------
	function page_is_indexed( $p_page ) {
		return ( page_valid_id( page_get_id( $p_page ) ) );
	}
	### --------------------
	function page_visible_notes_count( $p_page_id ) {
		$c_page_id = db_prepare_int( $p_page_id );

		$query = "SELECT COUNT(*)
				FROM " . config_get( 'phpWN_note_table' ) . "
				WHERE page_id=$c_page_id AND visible=" . NOTE_VISIBLE_ACCEPTED;
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	### --------------------
	function page_notes_count( $p_page_id ) {
		$c_page_id = db_prepare_int( $p_page_id );

		$query = "SELECT COUNT(*)
				FROM " . config_get( 'phpWN_note_table' ) . "
				WHERE page_id=$c_page_id";
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	### --------------------
	function page_get_name( $p_page_id ) {
		$c_page_id = db_prepare_int( $p_page_id );

		$query = "SELECT page
				FROM " . config_get( 'phpWN_page_table' ) . "
				WHERE id=$c_page_id
				LIMIT 1";

		$result = db_query( $query );
		if ( db_num_rows( $result) > 0 ) {
			return db_result( $result, 0, 0 );
		}

		return false;
	}
	### --------------------
	function page_update_url( $p_page_id, $p_url ) {
		$t_url = page_get_info( page_where_id_equals( $p_page_id ), 'url' );
		if ( $t_url === $p_url ) {
		    return;
		}

		# @@@@ If the information is the same, then don't update/touch.

		$c_page_id = db_prepare_int( $p_page_id );
		$c_url = db_prepare_string( $p_url );

		$query = "UPDATE " . config_get( 'phpWN_page_table' ) . "
				SET url='$c_url'
				WHERE id=$c_page_id LIMIT 1";
		$result = db_query( $query );
		page_touch( $p_page_id );
	}
	### --------------------
	function page_update_neighbours( $p_page_id, $p_prev, $p_next, $p_parent ) {
		if ( ( null === $p_prev ) && ( null === $p_next ) && ( null === $p_parent ) ) {
		    return;
		}

		$t_page_info = page_get_info( page_where_id_equals( $p_page_id ) );
		if ( false === $t_page_info ) {
		    return;
		}

		if ( null === $p_parent ) {
		    $t_parent_id = $t_page_info['parent_id'];
		} else {
			$t_parent_id = page_get_id( $p_parent );
			if ( false === page_valid_id( $t_parent_id ) ) {
			    $t_parent_id = 0;
			}
		}

		if ( null === $p_prev ) {
		    $t_prev_id = $t_page_info['prev_id'];
		} else {
			$t_prev_id = page_get_id( $p_prev );
			if ( false === page_valid_id( $t_prev_id ) ) {
			    $t_prev_id = 0;
			}
		}

		if ( null === $p_next ) {
		    $t_next_id = $t_page_info['next_id'];
		} else {
			$t_next_id = page_get_id( $p_next );
			if ( false === page_valid_id( $t_next_id ) ) {
			    $t_next_id = 0;
			}
		}

		# If the information is the same, then don't update/touch.
		if ( ( $t_parent_id == $t_page_info['parent_id'] ) &&
			( $t_prev_id == $t_page_info['prev_id'] ) && 
			( $t_next_id == $t_page_info['next_id'] ) ) {
			return;
		}

		$c_page_id = db_prepare_int( $p_page_id );

		$query = "UPDATE " . config_get( 'phpWN_page_table' ) . "
				SET parent_id=$t_parent_id, prev_id=$t_prev_id, next_id=$t_next_id
				WHERE id=$c_page_id LIMIT 1";
		$result = db_query( $query );
		page_touch( $p_page_id );
	}
	### --------------------
	# Update the last modified time stamp for the page.
	function page_touch( $p_page_id ) {
		$c_page_id = db_prepare_int( $p_page_id );

		$query ='UPDATE ' . config_get( 'phpWN_page_table') . ' ' .
				"SET last_updated=NOW() " .
				"WHERE id=$c_page_id " .
				"LIMIT 1";

		return ( false !== db_query( $query ) );
	}
	### --------------------
	### Allows for path navigation to choose base dir
	function print_dirs( $p_path, $p_php_self ) {
		global $g_admin_index_files;
		
		echo '<table>';

		$handle = opendir( $p_path );
		while ( $file = readdir( $handle ) ) {
			if ( is_dir( $p_path . $file ) && ( $file != '.' ) ) {
				if ( $file == '..' ) {
					$t_dir = dirname( $p_path );
				} else {
					$t_dir = $p_path . $file;
				}
				$t_dir = urlencode( $t_dir );
				echo "<tr><td></td><td><a href=\"$g_admin_index_files?f_dir=$t_dir\">[$file]</a></td></tr>";
			}
		}
		closedir( $handle );
		
		$handle = opendir( $p_path );
		while ( $file = readdir( $handle ) ) {
			if ( !is_dir( $p_path . $file ) ) {
				$t_filename = $p_path . $file;
				$t_id = page_get_id( $t_filename );
				#echo "<a href=\"$g_admin_index_files?f_dir=$t_file\">$file</a><br />";
				$t_add = !page_valid_id( $t_id );
				if ( !$t_add ) {
				    $t_count = '(' . page_visible_notes_count( $t_id ) . ')';
				} else {
					$t_count = '';
				}

				echo "<tr><td>" . link_page_action( $t_filename, 'index', $p_php_self, $t_add ) . ' ' . link_page_action( $t_id, 'unindex', $p_php_self, !$t_add ). "</td><td><tt>$file$t_count</tt></td></tr>";
			}
		}
		closedir( $handle );
		
		echo '</table>';
	}
	### --------------------
	function page_add( $p_page_name ) {
		# if page already exists, return to avoid duplicates
		if ( page_get_id( $p_page_name ) !== false ) {
		    return 0;
		}

		$c_page_name = db_prepare_string( $p_page_name );

		$query = "INSERT INTO " . config_get( 'phpWN_page_table' ) . "
				( id, date_indexed, last_updated, page )
				VALUES
				( null, NOW(), NOW(), '$c_page_name' )";
		$result = db_query( $query );

		return $result;
	}
	### --------------------
	function page_add_dir( $p_path='', $p_recursive=true ) {
		$dirs = array();
		$files = array();

		$handle = opendir( $p_path );
		while ( $file = readdir( $handle ) ) {
			if ( ( $file == '.' ) || ( $file == '..' ) ) {
				continue;
			}

			if ( is_dir( $p_path . $file ) ) {
				$dirs[] = $file;
			} else {
				$files[] = $file;
			}
		}
		closedir( $handle );
		sort( $dirs );
		sort( $files );

		foreach ( $files as $file ) {
			$t_filename = $p_path . $file;
			page_add( $t_filename );
		}

		# if not recursive return before processing sub-directories
		if ( !$p_recursive ) {
		    return;
		}

		foreach ( $dirs as $dir ) {
			page_add_dir( $p_path . $dir . DIRECTORY_SEPARATOR );
		}
	}
	### --------------------
	function page_delete_notes( $p_page_id ) {
		$c_page_id = db_prepare_int( $p_page_id );

		$query = "DELETE FROM " . config_get( 'phpWN_note_table' ) . "
				WHERE page_id=$c_page_id";

		$result = db_query( $query );

		return true;
	}
	### --------------------
	function page_delete( $p_page_id ) {
		if ( !page_delete_notes( $p_page_id ) ) {
		    return false;
		}

		$c_page_id = db_prepare_int( $p_page_id );

		$query = "DELETE FROM " . config_get( 'phpWN_page_table' ) . "
				WHERE id=$c_page_id
				LIMIT 1";

		$result = db_query( $query );

		return true;
	}
	### --------------------
	function page_prepare_theme_data( $p_page_id ) {
		$t_page_data = array();

		$t_page_info = page_get_info( page_where_id_equals( $p_page_id ) );
		if ( false === $t_page_info ) {
			return (false);
		}

		$t_page_data['id'] = $t_page_info['id'];
		$t_page_data['page'] = $t_page_info['page'];
		$t_page_data['url'] = $t_page_info['url'];
		$t_page_data['last_updated'] = $t_page_info['last_updated'];
		$t_page_data['preview'] = false;

		$t_prev_page = page_get_info( page_where_id_equals( $t_page_info['prev_id'] ) );
		$t_next_page = page_get_info( page_where_id_equals( $t_page_info['next_id'] ) );

		if ( false === $t_prev_page ) {
		    $t_page_data['prev_page'] = '';
		    $t_page_data['prev_url'] = '';
		} else {
		    $t_page_data['prev_page'] = $t_prev_page['page'];
		    $t_page_data['prev_url'] = $t_prev_page['url'];
		}

		if ( false === $t_next_page ) {
		    $t_page_data['next_page'] = '';
		    $t_page_data['next_url'] = '';
		} else {
		    $t_page_data['next_page'] = $t_next_page['page'];
		    $t_page_data['next_url'] = $t_next_page['url'];
		}

		$t_page_data['notes'] = note_get_all_visible( $p_page_id );

		return( $t_page_data );
	}
	### --------------------
	function page_visit( $p_page_id ) {
		$c_page_id = db_prepare_int( $p_page_id );
		$query ='UPDATE ' . config_get( 'phpWN_page_table') . ' ' .
				"SET visits=visits+1 " .
				"WHERE id=$c_page_id " .
				"LIMIT 1";
		return ( false !== db_query( $query ) );
	}
	### --------------------
	function page_visits_count( $p_page_id ) {
		$c_page_id = db_prepare_int( $p_page_id );

		$query = "SELECT visits
				FROM " . config_get( 'phpWN_page_table' ) . "
				WHERE id=$c_page_id
				LIMIT 1";

		$result = db_query( $query );
		if ( db_num_rows( $result) > 0 ) {
			return db_result( $result, 0, 0 );
		}

		return false;
	}
	### --------------------

?>
