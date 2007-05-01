<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	#####################
	# VERSION SETTINGS
	#####################

	$g_phpWebNotes_version = '2.0.0-dev';

	### Display phpWebNotes version on pages
	$g_show_version = ON;

	######################
	# DATABASE SETTINGS
	######################

	$g_hostname = 'localhost';
	$g_db_username = 'root';
	$g_db_password = '';
	$g_database_name = 'phpWebNotes';

	### Database Table Names
	$g_phpWN_note_table = 'phpWN_note_table';
	$g_phpWN_page_table = 'phpWN_page_table';
	$g_phpWN_user_table = 'phpWN_user_table';

	####################
	# SERVER SETTINGS
	####################

	# Using Microsoft Internet Information Server (IIS)
	$g_use_iis = OFF;

	### File extension to use.  Default is .php.
	$g_ext = '.php';

	### url directory
	$g_web_directory = '/webnotes/';

	### absolute directory path
	$g_absolute_directory = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR;

	######################
	# COOKIES' SETTINGS
	######################

	### Cookies
	$g_string_cookie = 'PHPWEBNOTES_COOKIE_STRING';

	### The url underwhich the cookie is visible
	$g_cookie_url = '/';

	##################
	# TIME SETTINGS
	##################

	### Time to wait between redirects (except index.html)
	$g_time_wait = 2;

	### This is how long the "save login" cookies live.
	$g_cookie_time_length = 30000000;  # 1 year

	#####################
	# DISPLAY SETTINGS
	#####################

	$g_window_title = 'phpWebNotes';
	$g_page_title = 'phpWebNotes';

	### default ordering of the notes.
	### ASC = newest on bottom
	$g_note_order = 'ASC';

	### optional page includes (for appearance customization)
	$g_top_page_inc = '';
	$g_bottom_page_inc = '';

	$g_date_format = 'm-d-y H:i';

	### change to language you want... choices are:
	### english
	$g_language = 'english';

	### Theme to be used
	$g_theme = 'phpnet';

	### Customize this file for the add message page
	$g_note_add_include = 'note_add_msg_inc.php';

	### Replace :) with icons [ON] or leave as text [OFF]
	$g_enable_smileys = ON;

	### Colors
	$g_table_border_color = '#aaaaaa';
	$g_table_title_color = '#cccccc';  # temporary color, should be changed
	$g_primary_dark_color = '#d8d8d8';
	$g_primary_light_color = '#e8e8e8';
	$g_white_color = '#ffffff';
	$g_header_color = '#bbddff';

	####################################
	# CACHING / OPTIMISATION SETTINGS
	####################################

	# minutes to wait before document is stale (in minutes)
	$g_content_expire = 0;

	###################
	# ADMIN SETTINGS
	###################

	# automatically index pages that call pwn_api APIs when visited for the first time.
	$g_auto_index_pages = ON;

	# automatically sets the e-mail field when logged in users are submitting notes.
	$g_auto_set_email = ON;

	########################
	# MODERATION SETTINGS
	########################

	$g_auto_accept_notes = OFF;

	################################
	# SECURITY AND AUTHENTICATION
	################################

	# AUTH_MD5, AUTH_CRYPT, AUTH_PLAIN
	$g_auth_type = AUTH_MD5;

	# allow users to signup for their own accounts
	$g_allow_signup = ON;

	# Access Levels
	# any user with an access level that is greater than or equal to the specified
	# threshold, will be able to perform the action.  If an action is to be disabled
	# for all access levels (including administrator) or to be only allowed for a
	# specified set of access levels ($g_access_sets), then it should be set to
	# NOBODY.
	$g_access_levels = array(
				ACTION_NOTES_VIEW_PENDING => MODERATOR,
				ACTION_NOTES_VIEW_ACCEPTED => EVERYBODY,
				ACTION_NOTES_VIEW_DECLINED => MODERATOR,
				ACTION_NOTES_VIEW_ARCHIVED => MODERATOR,
				ACTION_NOTES_VIEW_DELETED => ADMINISTRATOR,
				ACTION_NOTES_SUBMIT => EVERYBODY,
				ACTION_NOTES_ADD => MODERATOR,
				ACTION_NOTES_EDIT => MODERATOR,
				ACTION_NOTES_EDIT_OWN => REGISTERED,
				ACTION_NOTES_DELETE_OWN => REGISTERED,
				ACTION_NOTES_MODERATE => MODERATOR,
				ACTION_NOTES_MODERATE_ACCEPT => MODERATOR,
				ACTION_NOTES_MODERATE_DECLINE => MODERATOR,
				ACTION_NOTES_MODERATE_ARCHIVE => MODERATOR,
				ACTION_NOTES_MODERATE_DELETE => MODERATOR,
				ACTION_NOTES_MODERATE_QUEUE => MODERATOR,
				ACTION_NOTES_PACK_DELETED => NOBODY,
				ACTION_NOTES_PACK_DECLINED => MODERATOR,
				ACTION_USERS_MANAGE => ADMINISTRATOR,
				ACTION_USERS_ADD => ADMINISTRATOR,
				ACTION_USERS_EDIT => ADMINISTRATOR,
				ACTION_USERS_EDIT_OWN => REGISTERED,
				ACTION_USERS_EDIT_OWN_PROTECTED => ADMINISTRATOR,
				ACTION_USERS_DELETE => ADMINISTRATOR,
				ACTION_PAGES_MANAGE => ADMINISTRATOR,
				ACTION_PAGES_ADD => ADMINISTRATOR,
				ACTION_PAGES_DELETE => ADMINISTRATOR );

	# This array specified for each action, the user types that can perform it.
	# This is more flexible than specifying a threshold.  This is only used when
	# the threshold is set to NOBODY for the specified action.
	# Added one example below (although this could have been done by setting
	# the threshold to ADMINISTRATOR.
	$g_access_sets = array(	ACTION_NOTES_PACK_DELETED => array( ADMINISTRATOR ) );

	###################
	# EMAIL SETTINGS
	###################

	# This option allows you to use a remote SMTP host.  Must use the phpMailer script
	# Name of smtp host, needed for phpMailer, taken from php.ini
	$g_smtp_host     = 'localhost';

	$g_webmaster_email = 'webmaster@nowhere';
	$g_administrator_email = 'admin@nowhere';

	# the "From: " field in emails
	$g_from_email           = 'noreply@nowhere';

	# the return address for bounced mail
	$g_return_path_email    = 'admin@nowhere';

	# if ON users will be sent their password when reset.
	# if OFF the password will be set to blank.
	$g_send_reset_password       = ON;

	# allow email notification
	$g_enable_email_notification = ON;

	# Set to OFF to remove X-Priority header
	$g_use_x_priority            = ON;

	# some Mail transfer agents (MTAs) don't like bare linefeeds...
	# or they take good input and create barelinefeeds
	# If problems occur when sending mail through your server try turning this OFF
	# more here: http://pobox.com/~djb/docs/smtplf.html
	$g_mail_send_crlf            = OFF;

	##########################
	# ENUMERATIONS SETTINGS
	##########################

	# --- enum strings ----------------
	# status from $g_status_index-1 to 79 are used for the onboard customization (if enabled)
	# directly use Mantis to edit them.
	$g_access_levels_enum_string = '10:anonymous,40:registered,70:moderator,90:administrator';
?>
