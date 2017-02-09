<?php

require_once(__DIR__.'/../common/code/bootstrap.php');

$releases = new BoostReleases(
    BOOST_WEBSITE_DATA_ROOT_DIR.'/generated/state/release.txt');

header('Content-Type: text/csv');
foreach($releases->release_data as $release => $data) {
    $parts = explode('-', $release, 2);
    $release_data = $releases->get_latest_release_data($parts[0], $parts[1]);
    $version = (string) $release_data['release']['version'];

    foreach(BoostWebsite::array_get($release_data['release'], 'downloads', array()) as $download) {
        $url = BoostWebsite::array_get($download, 'url');
        $hash = BoostWebsite::array_get($download, 'sha256');
        if ($hash) {
            echo "{$version},{$url},{$hash}\n";
        }
    }
}
