#!/usr/bin/env php
<?php
# Copyright 2007 Rene Rivera
# Copyright 2011, 2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)

$usage = <<<EOL
Usage: {} [--in-progress-only]

Update the html pages and rss feeds for new or updated quickbook files.

Flags:

    --in-progress-only      Only update the 'in progress' page.
EOL;

require_once(__DIR__.'/../common/code/bootstrap.php');

$options = BoostSiteTools\CommandLineOptions::parse($usage, array(
    'in-progress-only' => false));

$site_tools = new BoostSiteTools();

if ($options->flags['in-progress-only']) {
    $site_tools->update_in_progress_pages();
} else {
    $site_tools->update_quickbook();
}
