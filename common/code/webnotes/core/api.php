<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	###########################################################################
	### INCLUDES                                                            ###
	###########################################################################

	$t_path_main = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR;
	$t_path_core = $t_path_main . 'core' . DIRECTORY_SEPARATOR;

	# The $g_ext can not be used before the custom config is included.
	require_once( $t_path_core . 'php_api.php' );
	require_once( $t_path_core . 'constants_inc.php' );
	require_once( $t_path_core . 'config_defaults_inc.php' );

	$t_custom_config = $t_path_core . 'custom_config_inc.php';
	if ( file_exists( $t_custom_config ) ) {
		require_once( $t_custom_config );
	}

	# Filenames
	$g_login						= $g_web_directory . 'login' . $g_ext;
	$g_login_page					= $g_web_directory . 'login_page' . $g_ext;
	$g_login_success_page			= $g_web_directory . 'admin' . $g_ext;
	$g_logout						= $g_web_directory . 'logout' . $g_ext;
	$g_logout_redirect_page			= $g_web_directory;
	$g_signup_page					= $g_web_directory . 'signup_page' . $g_ext;

	$g_admin_index_files			= $g_web_directory . 'admin_index_files' . $g_ext;
	$g_admin_view_queue				= $g_web_directory . 'admin_view_queue' . $g_ext;
	$g_admin_manage_notes			= $g_web_directory . 'admin_manage_notes' . $g_ext;
	$g_admin_manage_users			= $g_web_directory . 'admin_manage_users' . $g_ext;
	$g_admin_manage_users_add_page	= $g_web_directory . 'admin_manage_users_add_page' . $g_ext;
	$g_admin_manage_users_add		= $g_web_directory . 'admin_manage_users_add' . $g_ext;
	$g_admin_manage_users_edit		= $g_web_directory . 'admin_manage_users_edit' . $g_ext;
	$g_admin_manage_users_update	= $g_web_directory . 'admin_manage_users_update' . $g_ext;
	$g_admin_manage_users_delete	= $g_web_directory . 'admin_manage_users_delete' . $g_ext;
	$g_admin_manage_users_delete_page	= $g_web_directory . 'admin_manage_users_delete_page' . $g_ext;
	$g_admin_change_password		= $g_web_directory . 'admin_change_password' . $g_ext;

	$g_user_home_page				= $g_web_directory . 'user_home_page' . $g_ext;
	$g_admin_page					= $g_user_home_page;

	$g_css_inc_file					= $g_absolute_directory . 'core' . DIRECTORY_SEPARATOR . 'css_inc' . $g_ext;
	$g_meta_inc_file				= $g_absolute_directory . 'core' . DIRECTORY_SEPARATOR . 'meta_inc' . $g_ext;

	$g_note_add_page				= $g_web_directory . 'note_add_page' . $g_ext;
	$g_note_preview_page			= $g_web_directory . 'note_preview_page' . $g_ext;
	$g_note_add						= $g_web_directory . 'note_add' . $g_ext;

	$g_about_page				= $g_web_directory . 'about_page' . $g_ext;

	$t_path_lang = $t_path_main . 'lang' . DIRECTORY_SEPARATOR;
	require_once( $t_path_lang . 'strings_english' . $g_ext );
	if( $g_language != 'english') {
		require_once( $t_path_lang . 'strings_' . $g_language . $g_ext );
	}

	require_once( $t_path_core . 'lang_api.php' );
	require_once( $t_path_core . 'config_api.php' );
	require_once( $t_path_core . 'database_api.php' );
	require_once( $t_path_core . 'note_api.php' );
	require_once( $t_path_core . 'string_api.php' );
	require_once( $t_path_core . 'access_api.php' );
	require_once( $t_path_core . 'page_api.php' );
	require_once( $t_path_core . 'html_api.php' );
	require_once( $t_path_core . 'user_api.php' );
	require_once( $t_path_core . 'link_api.php' );
	require_once( $t_path_core . 'util_api.php' );
	require_once( $t_path_core . 'gpc_api.php' );
	require_once( $t_path_core . 'email_api.php' );
	require_once( $t_path_core . 'enum_api.php' );
	require_once( $t_path_core . 'pwn_api.php' );
	require_once( $t_path_main . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $g_theme .
					DIRECTORY_SEPARATOR . 'theme_api.php' );

	# Cookies
	$g_string_cookie_val = gpc_get_cookie( $g_string_cookie, '' );

	###########################################################################
	### END                                                                 ###
	###########################################################################
?>