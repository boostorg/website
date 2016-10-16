#!/usr/bin/env php
<?php
# Copyright 2007 Rene Rivera
# Copyright 2011, 2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

$usage = <<<EOL
Usage: {}

Update the html pages and rss feeds for new or updated quickbook files.
EOL;

require_once(__DIR__.'/../common/code/bootstrap.php');

BoostSiteTools\CommandLineOptions::parse($usage);

$site_tools = new BoostSiteTools(__DIR__.'/..');
$site_tools->update_quickbook();
