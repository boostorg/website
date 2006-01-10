#
# Upgrade phpWebNotes 1.0.0 to phpWebNotes 2.0.0pr1
#
ALTER TABLE `phpWN_user_table` CHANGE `password` `password` VARCHAR( 32 ) NOT NULL;
ALTER TABLE `phpWN_user_table` CHANGE `access_level` `access_level` INT( 2 ) DEFAULT '40' NOT NULL;
ALTER TABLE `phpWN_page_table` ADD `url` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `phpWN_page_table` ADD `prev_id` INT( 10 ) UNSIGNED ZEROFILL NOT NULL ;
ALTER TABLE `phpWN_page_table` ADD `next_id` INT( 10 ) UNSIGNED ZEROFILL NOT NULL ;
ALTER TABLE `phpWN_page_table` ADD `last_updated` DATETIME NOT NULL AFTER `date_indexed` ;
ALTER TABLE `phpWN_page_table` ADD `parent_id` INT( 10 ) UNSIGNED ZEROFILL NOT NULL AFTER `url` ;
ALTER TABLE `phpWN_page_table` ADD INDEX ( `parent_id` ) ;
ALTER TABLE `phpWN_page_table` DROP INDEX `id` ;
ALTER TABLE `phpWN_note_table` DROP INDEX `id` ;

#
# Upgrade 2.0.0pr1 to latest
#
ALTER TABLE `phpwn_user_table` ADD `protected` INT( 1 ) DEFAULT '0' NOT NULL AFTER `enabled` ;
ALTER TABLE `phpWN_page_table` ADD `visits` INT(10) NOT NULL;
ALTER TABLE `phpwn_user_table` CHANGE `cookie_string` `cookie_string` VARCHAR( 64 ) NOT NULL ;