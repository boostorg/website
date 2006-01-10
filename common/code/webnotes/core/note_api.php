<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	### --------------------
	function note_where_id_equals( $p_note_id ) {
		$c_note_id = db_prepare_int( $p_note_id );
		return ("(n.id=$c_note_id)");
	}
	### --------------------
	function note_where_page_and_visibility_equals( $p_page_id, $p_visibility ) {
		$c_page_id = db_prepare_int( $p_page_id );
		$c_visibility = db_prepare_int( $p_visibility );
		return ("((n.page_id=$c_page_id) AND (n.visible=$c_visiblity))");
	}
	### --------------------
	# $p_where is constructed by note_where* and hence does not need to be cleaned.
	function note_get_info ( $p_where, $p_field = null ) {
		$query = "SELECT *, UNIX_TIMESTAMP(date_submitted) as date_submitted
				FROM " . config_get( 'phpWN_note_table' ) . " n,
					" . config_get( 'phpWN_page_table' ) . " p
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
	# allow an array of visibilities as a parameter
	function note_queue_count() {
		# the reason of including the page is to avoid counting orphan
		# notes.
		$query = "SELECT COUNT(*)
				FROM " . config_get( 'phpWN_note_table' ) . " n, 
					" . config_get( 'phpWN_page_table' ) . " p
				WHERE n.page_id = p.id AND 
				visible=" . NOTE_VISIBLE_PENDING;
		$result = db_query( $query );
		return db_result( $result, 0, 0 );
	}
	### --------------------
	function note_add( $p_page_id, $p_email, $p_remote_addr, $p_note ) {
		note_ensure_mandatory_fields( $p_email, $p_note );

		if ( ON == config_get('auto_accept_notes') ) {
			$t_visible = NOTE_VISIBLE_ACCEPTED;
		} else {
			$t_visible = NOTE_VISIBLE_PENDING;
		}

		$c_page_id = db_prepare_int( $p_page_id );
		$c_email = db_prepare_string( $p_email );
		$c_note = db_prepare_string( $p_note );
		$c_remote_address = db_prepare_string( $p_remote_addr );

		# @@@@ Also set last-updated field

		$query = "INSERT INTO " . config_get( 'phpWN_note_table' ) . "
	    		( id, page_id, email, ip, date_submitted, note, visible )
				VALUES
				( null, $c_page_id, '$c_email', '$c_remote_address', NOW(), '$c_note', $t_visible )";
		$result = db_query( $query );
		$result = db_insert_id();

		page_touch( $p_page_id );

		return ( $result );
	}
	### --------------------
	function note_get_visibility_str( $p_visible ) {
		switch ( $p_visible ) {
			case NOTE_VISIBLE_PENDING:
				return "Pending";
			case NOTE_VISIBLE_ACCEPTED:
				return "Accepted";
			case NOTE_VISIBLE_DECLINED:
				return "Declined";
			case NOTE_VISIBLE_ARCHIVED:
				return "Archived"; 
			case NOTE_VISIBLE_DELETED:
				return "Deleted";
			default:
				return "Unknown";
		}
	}
	### --------------------
	function note_update_visibility( $p_id, $p_visibility ) {
		$c_id = db_prepare_int( $p_id );
		$c_visibility = db_prepare_int( $p_visibility );

		$query = "UPDATE " . config_get( 'phpWN_note_table' ) . "
				SET visible=$c_visibility
				WHERE id=$c_id LIMIT 1";
		$result = db_query( $query );

		note_touch( $p_id );
	}
	### --------------------
	# Put back as pending if approved by default.
	function note_pending( $p_id ) {
		note_update_visibility( $p_id, NOTE_VISIBLE_PENDING );
	}
	### --------------------
	function note_accept( $p_id ) {
		note_update_visibility( $p_id, NOTE_VISIBLE_ACCEPTED );
	}
	### --------------------
	function note_decline( $p_id ) {
		note_update_visibility( $p_id, NOTE_VISIBLE_DECLINED );
	}
	### --------------------
	function note_archive( $p_id ) {
		note_update_visibility( $p_id, NOTE_VISIBLE_ARCHIVED );
	}
	### --------------------
	function note_delete( $p_id ) {
		note_update_visibility( $p_id, NOTE_VISIBLE_DELETED );
	}
	### --------------------
	function note_pack_deleted() {
		$query = "DELETE FROM " . config_get( 'phpWN_note_table' ) . "
				WHERE visible=" . NOTE_VISIBLE_DELETED;
		$result = db_query( $query );
	}
	### --------------------
	function note_ensure_mandatory_fields( $p_email, $p_note ) {
		if ( trim( $p_email ) == '' )
		{
			echo sprintf( 'Mandatory field &quot;%s&quot; missing.', 'email');
			exit;
		}

		if ( trim( $p_note ) == '' )
		{
			echo sprintf( 'Mandatory field &quot;%s&quot; missing.', 'note');
			exit;
		}

	}
	### --------------------
	function note_update( $p_id, $p_email, $p_note ) {
		note_ensure_mandatory_fields( $p_email, $p_note );

		$c_id = db_prepare_int( $p_id );
		$c_email = db_prepare_string( $p_email );
		$c_note = db_prepare_string( $p_note );

		$query = "UPDATE " . config_get( 'phpWN_note_table' ) . "
				SET email='$c_email', note='$c_note'
				WHERE id=$c_id LIMIT 1";
		$result = db_query( $query );

		note_touch( $p_id );
		
		return ( $result );
	}
	### --------------------
	function note_get_page_id( $p_note_id ) {
		$t_note_info = note_get_info( note_where_id_equals ( $p_note_id ) );
		if ( false === $t_note_info ) {
		    return false;
		}

		return $t_note_info['page_id'];
	}
	### --------------------
	function note_touch( $p_note_id, $p_page_id = null ) {
		if ( null === $p_page_id ) {
		    $p_page_id = note_get_page_id( $p_note_id );
		}

		page_touch( $p_page_id );
	}
	### --------------------
	function note_get_all_visible( $p_page_id ) {
		$notes = array();

		$t_page_info = page_get_info( page_where_id_equals( $p_page_id ) );
		if ( false === $t_page_info ) {
			return false;
		}

		$c_page_id = db_prepare_int( $p_page_id );

		$query = "SELECT *, UNIX_TIMESTAMP(date_submitted) as date_submitted
				FROM " . config_get( 'phpWN_note_table' ) . "
				WHERE page_id=$c_page_id
				ORDER BY date_submitted " . config_get( 'note_order' );

		$result = db_query( $query );
		while ( $row = db_fetch_array( $result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			if ( ( NOTE_VISIBLE_PENDING == $v_visible ) && ( access_check_action( ACTION_NOTES_VIEW_PENDING ) === false ) ) {
				continue;
			}

			if ( ( NOTE_VISIBLE_ACCEPTED == $v_visible ) && ( access_check_action( ACTION_NOTES_VIEW_ACCEPTED ) === false ) ) {
				continue;
			}

			if ( ( NOTE_VISIBLE_DECLINED == $v_visible ) && ( access_check_action( ACTION_NOTES_VIEW_DECLINED ) === false ) ) {
				continue;
			}

			if ( ( NOTE_VISIBLE_ARCHIVED == $v_visible ) && ( access_check_action( ACTION_NOTES_VIEW_ARCHIVED ) === false ) ) {
				continue;
			}

			if ( ( NOTE_VISIBLE_DELETED == $v_visible ) && ( access_check_action( ACTION_NOTES_VIEW_DELETED ) === false ) ) {
				continue;
			}

			$info['visible'] = $v_visible;
			$info['id'] = $v_id;
			$info['email'] = string_prepare_note_for_viewing ( $v_email, $t_page_info['url'] );
			$info['note'] = string_prepare_note_for_viewing ( $v_note, $t_page_info['url'] );

			$info['date'] = $v_date_submitted;

			$notes[] = $info;
		}

		return( $notes );
	}
	### --------------------
	# @@@@ Should be obsolete soon!
	function note_queue( $p_only_one = true ) {
		$query = "SELECT n.id as note_id, n.*, p.page
				FROM " . config_get( 'phpWN_note_table' ) . " n,
					" . config_get( 'phpWN_page_table' ) . " p
				WHERE n.visible=" . NOTE_VISIBLE_PENDING . " 
				AND n.page_id=p.id";

		if ( $p_only_one ) {
			$query .= ' LIMIT 1';
		} else {
			$query .= ' ORDER BY p.page, n.date_submitted';
		}

		return db_query( $query );
	}
	### --------------------
?>
