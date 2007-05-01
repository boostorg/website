<?php
        # phpWebNotes - a php based note addition system
        # Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
        # This program is distributed under the terms and conditions of the GPL
        # See the files README and LICENSE for details

	###########################################################################
	# Email API
	###########################################################################

	# --------------------
	# Send password to user	
	function email_signup( $p_username, $t_password, $p_email ) {
		# Email Strings
		$s_new_account_subject = "Your new user account";
		$s_new_account_greeting = "Greetings and welcome to the WebNotes.  Here is the information you need to login\n\n";
		$s_new_account_url = "You can login to the site here: ";
		$s_new_account_username = "Username: ";
		$s_new_account_password = "Password: ";
		$s_new_account_message = "After logging into the site please change your password.  Also note that your password is stored via one way encryption.  The staff cannot retrieve your password.  If you forget your password it will have to be reset.\n\n";
		$s_new_account_do_not_reply = "Do not reply to this message.\n";

		# Build Welcome Message
		$t_message = $s_new_account_greeting.
						$s_new_account_username.$p_username."\n".
						$s_new_account_password.$t_password."\n\n".
						$s_new_account_message.
						$s_new_account_do_not_reply;

		email_send( $p_email, $s_new_account_subject, $t_message );
	}
	# --------------------
	# this function sends the actual email
	function email_send( $p_recipient, $p_subject, $p_message, $p_header='' ) {
		global $g_from_email, $g_enable_email_notification,
				$g_return_path_email, $g_use_x_priority,
				$g_phpWebNotes_version;

		# short-circuit if no emails should be sent
		if ( OFF == $g_enable_email_notification ) {
			return;
		}

		$t_recipient = trim( $p_recipient );
		$t_subject   = trim( $p_subject );
		$t_message   = trim( $p_message );

		# Visit http://www.php.net/manual/function.mail.php
		# if you have problems with mailing
			
		$t_headers = "From: $g_from_email\r\n";

		$t_headers .= "X-Sender: <$g_from_email>\r\n";
		$t_headers .= "X-Mailer: phpWebNotes $g_phpWebNotes_version\r\n";
		if ( ON == $g_use_x_priority ) {
			$t_headers .= "X-Priority: 0\r\n";    # Urgent = 1, Not Urgent = 5, Disable = 0
		}
		$t_headers .= "Return-Path: <$g_return_path_email>\r\n";          # return email if error

		# If you want to send foreign charsets
		# $t_headers .= "Content-Type: text/html; charset=iso-8859-1\r\n";

		$t_headers .= $p_header . "\r\n";

		$t_recipient = email_make_lf_crlf( $t_recipient );
		$t_subject = email_make_lf_crlf( $t_subject );
		$t_message = email_make_lf_crlf( $t_message );
		$t_headers = email_make_lf_crlf( $t_headers );
		$result = mail( $t_recipient, $t_subject, $t_message, $t_headers );
		if ( false === $result ) {
			echo "PROBLEMS SENDING MAIL TO: $t_recipient<br />";
			echo htmlspecialchars($t_recipient).'<br />';
			echo htmlspecialchars($t_subject).'<br />';
			echo nl2br(htmlspecialchars($t_headers)).'<br />';
			#echo nl2br(htmlspecialchars($t_message)).'<br />';
			exit;
		}
	}
	# --------------------
	# clean up LF to CRLF
	function email_make_lf_crlf( $p_string ) {
		if ( OFF == config_get( 'mail_send_crlf' ) ) {
			return $p_string;
		}

		$p_string = str_replace( "\n", "\r\n", $p_string );
		return str_replace( "\r\r\n", "\r\n", $p_string );
	}
	# --------------------
	# email build note message
	function email_build_note_message( $p_note_id, &$subject, &$content ) {
		$note = note_get_info( note_where_id_equals( $p_note_id ) );
		if ( $note === false ) {
			return false;
		}
		extract( $note, EXTR_PREFIX_ALL, 'note' );

		$page = page_get_info( page_where_id_equals( $note_page_id ) );
		if ( $page === false ) {
			return false;
		}
		extract( $page, EXTR_PREFIX_ALL, 'page' );

		$subject  = "[$page_page] $note_email";

		$content  = '';
		$content .= str_pad( '', 70, '=' ) . "\n";
		$content .= 'http://' . $_SERVER['SERVER_NAME'] . $page_url . "\n";
		$content .= str_pad( '', 70, '-' ) . "\n";
		$content .= "Note Id: $note_id\n";
		$content .= "Email: $note_email\n";
		$content .= "IP: $note_ip\n";
		$content .= "Date Submitted: " . date( 'd-M-Y H:i:s', $note_date_submitted ) . "\n";
		$content .= "Visible: " . ( $note_visible ? "Yes" : "No" ) . "\n";
		$content .= str_pad( '', 70, '-' ) . "\n";
		$content .= $note_note . "\n";
		$content .= str_pad( '', 70, '=' ) . "\n";

		return true;
	}

	# --------------------
	# build an array of recipients
	function email_recipients( $p_note_id )
	{
		global $g_phpWN_user_table;

		$query = "SELECT email FROM $g_phpWN_user_table
				WHERE access_level >= " . MODERATOR .
					" AND email <> ''";
		$result = db_query( $query );

		$emails_array = array();
		while( $row = db_fetch_array( $result ) ) {
			$emails_array[] = $row['email'];
		}

		$emails_array = array_unique( $emails_array );
		$emails = implode( ',', $emails_array );

		return $emails;
	}

	# --------------------
	# email note to administrator
	# @@@ Query the database to send to moderators / administrators, rather than
	#     just the administrator in the configs.
	function email_note_added( $p_note_id ) {
		$subject = '';
		$content = '';
		email_build_note_message( $p_note_id, $subject, $content );

		$t_recipients = email_recipients( $p_note_id );

		global $g_administrator_email;
		email_send( $t_recipients, $subject, $content );
	}

	# --------------------
	# email note to administrator
	# @@@ Query the database to send to moderators / administrators, rather than
	#     just the administrator in the configs.
	function email_note_updated( $p_note_id ) {
		email_note_added( $p_note_id );
	}
?>
