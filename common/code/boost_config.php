<?php
/*
  Copyright 2005-2007 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

switch ($_SERVER['HTTP_HOST'])
{
  case 'boost.org':
  case 'www.boost.org':
  case 'beta.boost.org':
  {
    define('BOOST_CONFIG_FILE','/home/grafik/www.boost.org/config.php');
    define('ARCHIVE_PREFIX', '/home/grafik/www.boost.org/boost_');
    define('UNZIP', '/usr/bin/unzip');
  }
  break;
  
  case 'localhost':
  case 'boost.borg.redshift-software.com':
  {
    define('BOOST_CONFIG_FILE','/DevRoots/Boost-SVN/website/workplace/config.php');
    define('ARCHIVE_PREFIX', '/DevRoots/Boost/boost_');
    define('UNZIP', 'unzip');
  }
  break;
}

define('ARCHIVE_FILE_PREFIX', 'boost_');

require_once(BOOST_CONFIG_FILE);

?>
