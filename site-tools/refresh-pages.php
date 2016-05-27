#!/usr/bin/env php
<?php
# Copyright 2007 Rene Rivera
# Copyright 2011, 2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

$usage = <<<EOL
Usage: php refresh-pages.php

Reconvert all the quickbook files and regenerate the html pages. Does
not update the rss feeds or add new pages. Useful for when quickbook,
the scripts or the templates have been updated.
EOL;

require_once(__DIR__.'/../common/code/boost.php');

$site_tools = new BoostSiteTools(__DIR__.'/..');
$site_tools->update_quickbook(true);
