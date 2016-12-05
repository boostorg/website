#!/usr/bin/env php
<?php

define('SET_RELEASE_STATUS_USAGE', "
Usage: {} version

Used to mark a version a released.

Example:

{} 1.63.0.beta1
{} 1.63.0
");

require_once(__DIR__.'/../common/code/bootstrap.php');

function main() {
    $options = BoostSiteTools\CommandLineOptions::parse(
        SET_RELEASE_STATUS_USAGE);

    if (!count($options->positional)) {
        echo $options->usage_message();
        exit(1);
    }

    $version = BoostVersion::from($options->positional[0]);

    $releases = new BoostReleases(__DIR__.'/../generated/state/release.txt');
    $releases->setReleaseStatus('boost', $version, 'released');
    $releases->save();

    // Trigger a rebuild of existing release notes.
    $boost_site_tools = new BoostSiteTools();
    $pages = $boost_site_tools->load_pages();
    foreach ($pages->pages as $page) {
        if (!$page->release_data) { continue; }
        if ($page->release_data['version']->base_version() === $version->base_version()) {
            $page->page_state = 'changed';
            // Set qbk_hash to null to ensure this definitely looks like an
            // actual change on the next run. Might be unnecessary, certainly
            // would be if beta releases had a better hash.
            $page->qbk_hash = null;
        }
    }
    $pages->save();
}

main();
