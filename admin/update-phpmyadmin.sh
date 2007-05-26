#!/bin/sh
set -e

PHPMYADMIN_VERSION='2.10.1'

phpmyadmin="phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages"

##
#  Fetch all the parts, and extract.
#  - phpMyAdmin - http://www.phpmyadmin.net/
##
wget -N "http://prdownloads.sourceforge.net/phpmyadmin/${phpmyadmin}.tar.bz2"
if test -e "${phpmyadmin}.tar.bz2" ; then
	rm -rf "${phpmyadmin}"
	bunzip2 -c "${phpmyadmin}.tar.bz2" | tar xvf -
else
	exit 1
fi

##
#  phpMyAdmin...
##
cd "${phpmyadmin}"
blowfish_secret=`date | md5sum | head -c 32`
cat - >>config.inc.php <<PHP
<?php
\$cfg['PmaAbsoluteUri'] = 'http://beta.boost.org/admin/db/';
\$cfg['blowfish_secret'] = '${blowfish_secret}';
\$i = 0;
\$i++;
\$cfg['Servers'][\$i] = \$cfg['Servers'][0];
\$cfg['Servers'][\$i]['host'] = 'localhost';
\$cfg['Servers'][\$i]['auth_type'] = 'http';
?>
PHP
rm -f "phpMyAdmin"
ln -s "${phpmyadmin}" "phpMyAdmin"
