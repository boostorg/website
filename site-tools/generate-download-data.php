<?php

require_once(__DIR__.'/../common/code/boost.php');

function main() {
    $site_tools = new BoostSiteTools(__DIR__.'/..');
    $pages = $site_tools->load_pages();

    $downloads = array();
    foreach($pages->pages as $path => $page) {
        if ($page->type === 'release') {
            if (strpos($path, 'unversioned.qbk') === false) {
                $version = BoostVersion::from($path);
                $version_name = (string) $version;
            }
            else {
                $version = null;
                $version_name = 'unversioned';
            }

            $download_table_data = $page->download_table_data() ?: array();

            if (is_string($download_table_data)) switch($download_table_data) {
            case 'http://sourceforge.net/project/showfiles.php?group_id=7586&package_id=8041&release_id=138112':
                $download_table_data = array(
                    'downloads' => array(
                        'unix' => array(
                            array( 'url' => 'https://sourceforge.net/projects/boost/files/boost/1.20.2/boost-1.20.2.tar.bz2' ),
                            array( 'url' => 'https://sourceforge.net/projects/boost/files/boost/1.20.2/boost-1.20.2.tar.gz' ),
                        ),
                        'windows' => array(
                            array( 'url' => 'https://sourceforge.net/projects/boost/files/boost/1.20.2/boost-1_20_2.zip' ),
                        ),
                    )
                );
                break;
            case 'http://sourceforge.net/projects/boost/files/boost-jam/3.1.18/':
                $download_table_data = array();
                break;
            default:
                echo "Unknown download: ", $download_table_data, "\n";
                exit(0);
            }
            $entry = array_merge(
                array(
                    'release_notes' => $path,
                    'release_status' => $page->get_release_status(),
                    'version' => (string) $version,
                    'documentation' => $page->get_documentation(),
                    'download_page' => $page->get_download_page(),
                ),
                $download_table_data);

            if (!empty($entry['downloads'])) {
                $x = $entry['downloads'];
                $entry['downloads'] = array();
                foreach($x as $line_endings => $y) {
                    foreach ($y as $download) {
                        $download['line_endings'] = $line_endings;
                        $entry['downloads'][] = $download;
                    }
                }
            }

            $downloads[$version_name] = $entry;
        }
    }

    file_put_contents(
        __DIR__.'/../feed/history/releases.json',
        json_encode($downloads, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

main();
