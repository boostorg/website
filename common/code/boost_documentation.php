<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/
require_once(dirname(__FILE__) . '/boost.php');

define('BOOST_DOCS_MODIFIED_DATE', 'Sat 07 Feb 2015 21:44:00 +0000');

class BoostDocumentation
{
    var $params;

    function __construct($params = Array()) {
        $this->params = $params;
    }

    function get_param($key, $default = null) {
        return array_key_exists($key, $this->params) ?
            $this->params[$key] : $default;
    }

    function documenation_path_details()
    {
        $pattern = $this->get_param('pattern', '@^[/]([^/]+)(?:[/](.*))?$@');
        $archive_dir = $this->get_param('archive_dir', STATIC_DIR);

        $this->archive_dir = $archive_dir;

        // Get Archive Location

        if (array_key_exists('PATH_INFO', $_SERVER) &&
                preg_match($pattern, $_SERVER["PATH_INFO"], $path_parts)) {
            if ($path_parts[1] === 'regression') {
                $version = null;
                $version_dir = 'regression';
            }
            else {
                try {
                    $version = BoostVersion::from($path_parts[1]);
                }
                catch(BoostVersion_Exception $e) {
                    BoostWeb::error_404($_SERVER["PATH_INFO"], 'Unable to find version.');
                    return;
                }
                $version_dir = is_numeric($path_parts[1][0]) ?
                    "boost_{$path_parts[1]}" : $path_parts[1];
            }

            $path = array_key_exists(2, $path_parts) ? $path_parts[2] : null;
        }
        else {
            $version = BoostVersion::current();
            $version_dir = $version->dir();
            $path = null;
        }

        return compact('archive_dir', 'version', 'version_dir', 'path');
    }

    function documentation_dir() {
        extract($this->documenation_path_details());
        return $archive_dir.'/'.$version_dir;
    }

    function display_from_archive($content_map = array())
    {
        extract($this->documenation_path_details());

        // Set default values

        $fix_dir = $this->get_param('fix_dir');
        $use_http_expire_date = $this->get_param('use_http_expire_date', false);

        $file = false;

        if ($fix_dir) {
            $fix_path = "{$fix_dir}/{$version_dir}/{$path}";

            if (is_file($fix_path) ||
                (is_dir($fix_path) && is_file("{$fix_path}/index.html")))
            {
                $file = $fix_path;
            }
        }

        if (!$file) {
            $file = $archive_dir . '/';
            $file = $file . $version_dir . '/' . $path;
        }

        // Only use a permanent redirect for releases (beta or full).

        $redirect_status_code = $version &&
            $version->is_numbered_release() ? 301 : 302;

        // Calculate expiry date if requested.

        $expires = null;
        if ($use_http_expire_date)
        {
            if (!$version) {
                $expires = "+1 week";
            }
            else {
                $compare_version = BoostVersion::from($version)->
                    compare(BoostVersion::current());
                $expires = $compare_version === -1 ? "+1 year" :
                    ($compare_version === 0 ? "+1 week" : "+1 day");
            }
        }

        // Last modified date

        if (!is_readable($file)) {
            BoostWeb::error_404($file, 'Unable to find file.');
            return;
        }

        $last_modified = max(
            strtotime(BOOST_DOCS_MODIFIED_DATE),        // last manual documenation update
            filemtime(dirname(__FILE__).'/boost.php'),  // last release (since the version number is updated)
            filemtime($file)                            // when the file was modified
        );

        // Check file exists.

        if (is_dir($file))
        {
            if(substr($file, -1) != '/') {
                $redirect = BoostUrl::resolve(basename($file).'/');
                header("Location: $redirect", TRUE, $redirect_status_code);
                return;
            }

            $found_file = NULL;
            if (is_readable("{$file}/index.html")) $found_file = 'index.html';
            else if (is_readable("{$file}/index.htm")) $found_file = 'index.htm';

            if ($found_file) {
                $file = $file.$found_file;
                $path = $path.$found_file;
            }
            else {
                if (!BoostWeb::http_headers('text/html', $last_modified, $expires))
                    return;

                $data = new BoostFilterData();
                $data->version = $version;
                $data->path = $path;
                $data->archive_dir = $archive_dir;
                $display_dir = new BoostDisplayDir($data);
                return $display_dir->display($file);
            }
        }

        // Choose filter to use

        $info_map = array_merge($content_map, array(
            array('','@[.](txt|py|rst|jam|v2|bat|sh|xml|xsl|toyxml)$@i','text','text/plain'),
            array('','@[.](qbk|quickbook)$@i','qbk','text/plain'),
            array('','@[.](c|h|cpp|hpp)$@i','cpp','text/plain'),
            array('','@[.]png$@i','raw','image/png'),
            array('','@[.]gif$@i','raw','image/gif'),
            array('','@[.](jpg|jpeg|jpe)$@i','raw','image/jpeg'),
            array('','@[.]svg$@i','raw','image/svg+xml'),
            array('','@[.]css$@i','raw','text/css'),
            array('','@[.]js$@i','raw','application/x-javascript'),
            array('','@[.]pdf$@i','raw','application/pdf'),
            array('','@[.](html|htm)$@i','raw','text/html'),
            array('','@(/|^)(Jamroot|Jamfile|ChangeLog|configure)$@i','text','text/plain'),
            array('','@[.]dtd$@i','raw','application/xml-dtd'),
            array('','@[.]json$@i','raw','application/json'),
            ));

        $preprocess = null;
        $extractor = null;
        $type = null;

        foreach ($info_map as $i)
        {
            if (preg_match($i[1],$path))
            {
                if ($i[0]) {
                    $min_version = BoostVersion::from($i[0]);
                    if ($min_version->compare(BoostVersion::page()) > 0) {
                        // This is after the current version.
                        continue;
                    }
                }

                $extractor = $i[2];
                $type = $i[3];
                $preprocess = isset($i[4]) ? $i[4] : NULL;
                break;
            }
        }

        if (!$extractor) {
            if (strpos($_SERVER['HTTP_HOST'], 'www.boost.org') === false) {
                BoostWeb::error_404($file, 'No extractor found for filename.');
            } else {
                BoostWeb::error_404($file);
            }
            return;
        }

        // Handle ETags and Last-Modified HTTP headers.

        // Output raw files.

        if($extractor == 'raw') {
            if (!BoostWeb::http_headers($type, $last_modified, $expires))
                return;

            if ($_SERVER['REQUEST_METHOD'] != 'HEAD') {
                readfile($file);
            }
        }
        else {
            // Read file from hard drive

            $content = file_get_contents($file);

            // Check if the file contains a redirect.

            if($type == 'text/html') {
                if($redirect = detect_redirect($content)) {
                    BoostWeb::http_headers('text/html', null, "+1 day");
                    header("Location: $redirect", TRUE, $redirect_status_code);
                    if($_SERVER['REQUEST_METHOD'] != 'HEAD') echo $content;
                    return;
                }
            }

            if (!BoostWeb::http_headers('text/html', $last_modified, $expires))
                return;

            // Finally process the file and display it.

            if($_SERVER['REQUEST_METHOD'] != 'HEAD') {
                if ($preprocess) {
                    $content = call_user_func($preprocess, $content);
                }

                $data = new BoostFilterData();
                $data->version = $version;
                $data->path = $path;
                $data->content = $content;
                $data->archive_dir = $archive_dir;
                echo_filtered($extractor, $data);
            }
        }
    }
}

//
// Filters
//

function echo_filtered($extractor, $data) {
    $name = "BoostFilter".underscore_to_camel_case($extractor);
    $extractor = new $name($data);
    $extractor->echo_filtered();
}

function underscore_to_camel_case($name) {
    return str_replace(' ','', ucwords(str_replace('_', ' ', $name)));
}

/*
 * HTML processing functions
 */

function detect_redirect($content)
{
    // Only check small files, since larger files are never redirects, and are
    // expensive to search.
    if(strlen($content) <= 2000 &&
        preg_match(
            '@<meta\s+http-equiv\s*=\s*["\']?refresh["\']?\s+content\s*=\s*["\']0;\s*URL=([^"\']*)["\']\s*/?>@i',
            $content, $redirect))
    {
        return BoostUrl::resolve($redirect[1]);
    }

    return false;
}

function latest_link($filter_data)
{
    if (!isset($filter_data->version)) {
        return;
    }

    $version = BoostVersion::from($filter_data->version);

    $current = BoostVersion::current();
    switch ($current->compare($version))
    {
    case 0:
        break;
    case 1:
        echo '<div class="boost-common-header-notice">';
        if (realpath("{$filter_data->archive_dir}/{$current->dir()}/{$filter_data->path}") !== false)
        {
            echo '<a class="boost-common-header-inner" href="/doc/libs/release/',$filter_data->path,'">',
                "Click here to view the latest version of this page.",
                '</a>';
        }
        else
        {
            echo '<a class="boost-common-header-inner" href="/doc/libs/">',
                "This is an old version of boost. ",
                "Click here for the latest version's documentation home page.",
                '</a>';
        }
        echo '</div>', "\n";
        break;
    case -1:
        echo '<div class="boost-common-header-notice">';
        echo '<span class="boost-common-header-inner">';
        echo 'This is the documentation for a development version of boost';
        echo '</span>';
        echo '</div>', "\n";
        break;
    }
}
