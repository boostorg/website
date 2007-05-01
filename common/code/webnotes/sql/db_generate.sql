# phpMyAdmin MySQL-Dump
# version 2.3.0
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Oct 07, 2002 at 12:58 AM
# Server version: 3.23.49
# PHP Version: 4.0.6
# Database : `phpWebNotes`
# --------------------------------------------------------

#
# Table structure for table `phpWN_note_table`
#

CREATE TABLE phpWN_note_table (
  id int(10) unsigned zerofill NOT NULL auto_increment,
  page_id int(10) unsigned zerofill NOT NULL default '0000000000',
  email varchar(128) NOT NULL default '',
  ip varchar(15) NOT NULL default '',
  date_submitted datetime default NULL,
  visible int(1) NOT NULL default '0',
  note text NOT NULL,
  PRIMARY KEY  (id),
  KEY visible (visible)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `phpWN_page_table`
#

CREATE TABLE phpWN_page_table (
  id int(10) unsigned zerofill NOT NULL auto_increment,
  date_indexed datetime default NULL,
  last_updated datetime NOT NULL default '0000-00-00 00:00:00',
  page varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  parent_id int(10) unsigned zerofill NOT NULL default '0000000000',
  prev_id int(10) unsigned zerofill NOT NULL default '0000000000',
  next_id int(10) unsigned zerofill NOT NULL default '0000000000',
  PRIMARY KEY  (id),
  UNIQUE KEY page (page),
  KEY parent_id (parent_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `phpWN_user_table`
#

CREATE TABLE phpWN_user_table (
  id int(10) unsigned zerofill NOT NULL auto_increment,
  username varchar(32) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  email varchar(64) NOT NULL default '',
  access_level int(2) NOT NULL default '40',
  enabled int(1) NOT NULL default '0',
  cookie_string varchar(32) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table 'phpWN_user_table'
#

INSERT INTO phpWN_user_table VALUES ( '0000000001', 'administrator', '63a9f0ea7bb98050796b649e85481845', '', '90', '1', '9eCxeTLdGjDpI149f9aca9f0ba076ce2');
