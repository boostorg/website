<?php
	# phpWebNotes - a php based note addition system
	# Copyright (C) 2000-2002 Webnotes Team - webnotes-devel@sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id$
	# --------------------------------------------------------

	###########################################################################
	### CONSTANTS                                                           ###
	###########################################################################

	define( 'ON',       1 );
	define( 'OFF',      0 );

	# Authentication Types
	define( 'AUTH_PLAIN',      0 );
	define( 'AUTH_CRYPT',      1 );
	define( 'AUTH_MD5',        2 );

	# User Levels (these are saved in the db)
	define( 'NOBODY',        100 );  # to disable an action completely (no user has access level 100)
	define( 'ADMINISTRATOR',  90 );
	define( 'MODERATOR',      70 );
	define( 'REGISTERED',     40 );
	define( 'ANONYMOUS',      10 );
	define( 'EVERYBODY',       0 );

	# Actions
	define( 'ACTION_NOTES_VIEW_PENDING',         0 );  # view pending notes
	define( 'ACTION_NOTES_VIEW_ACCEPTED',        1 );  # view accepted notes
	define( 'ACTION_NOTES_VIEW_DECLINED',        2 );  # view declined notes
	define( 'ACTION_NOTES_VIEW_ARCHIVED',        3 );  # view archived notes
	define( 'ACTION_NOTES_VIEW_DELETED',         4 );  # view deleted notes
	define( 'ACTION_NOTES_SUBMIT',              10 );  # add as pending
	define( 'ACTION_NOTES_ADD',                 11 );  # add as accepted
	define( 'ACTION_NOTES_EDIT',                20 );  # edit all notes that are viewable
	define( 'ACTION_NOTES_EDIT_OWN',            21 );  # edit notes submitted by yourself
	define( 'ACTION_NOTES_DELETE',              30 );  # delete all notes that are viewable
	define( 'ACTION_NOTES_DELETE_OWN',          31 );  # delete notes submitted by yourself
	define( 'ACTION_NOTES_MODERATE',            40 );  # can done some moderation
	define( 'ACTION_NOTES_MODERATE_QUEUE',      41 );  # move notes to pending state
	define( 'ACTION_NOTES_MODERATE_ACCEPT',     42 );  # move notes to accepted state
	define( 'ACTION_NOTES_MODERATE_DECLINE',    43 );  # move notes to declined state
	define( 'ACTION_NOTES_MODERATE_ARCHIVE',    44 );  # move notes to archived state
	define( 'ACTION_NOTES_MODERATE_DELETE',     45 );  # move notes to deleted state
	define( 'ACTION_NOTES_PACK_DELETED',        50 );  # purge notes that are marked for deletion
	define( 'ACTION_NOTES_PACK_DECLINED',       51 );  # purge notes that are declined

	define( 'ACTION_USERS_MANAGE',    101 );
	define( 'ACTION_USERS_ADD',       102 );
	define( 'ACTION_USERS_EDIT',      103 );
	define( 'ACTION_USERS_EDIT_OWN',  104 );
	define( 'ACTION_USERS_EDIT_OWN_PROTECTED',  105 );
	define( 'ACTION_USERS_DELETE',    106 );

	define( 'ACTION_PAGES_MANAGE',    201 );
	define( 'ACTION_PAGES_ADD',       202 );
	define( 'ACTION_PAGES_DELETE',    203 );

	# Note Visible States (these are saved in the db)
	define( 'NOTE_VISIBLE_PENDING',     0 );
	define( 'NOTE_VISIBLE_ACCEPTED',    1 );
	define( 'NOTE_VISIBLE_DECLINED',    2 );
	define( 'NOTE_VISIBLE_ARCHIVED',    3 );
	define( 'NOTE_VISIBLE_DELETED',     4 );
?>