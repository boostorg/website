<?php
/*
  Copyright 2005-2007 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

/*
BOOST_CONFIG_FILE
  Path to local configuration, as a PHP source file.

BOOST_RSS_DIR
  Path to directory with RSS feeds from Gmane.

ARCHIVE_PREFIX
  Partial path for Boost release archives, the ZIP versions.

UNZIP
  Unzip program to use to extract files from ZIPs.

ARCHIVE_FILE_PREFIX
  Prefix for the root directory in the Boost ZIP archives.
*/

switch ($_SERVER['HTTP_HOST'])
{
  case 'boost.org':
  case 'www.boost.org':
  case 'live.boost.org':
  case 'beta.boost.org':
  {
    define('BOOST_CONFIG_FILE','/home/grafik/www.boost.org/config.php');
  }
  break;

  default:
  {
    define('BOOST_CONFIG_FILE',dirname(__FILE__) . '/boost_config_local.php');
  }
}

define('ARCHIVE_FILE_PREFIX', '');

require_once(BOOST_CONFIG_FILE);

?>
