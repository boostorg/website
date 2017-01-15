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
    var $archive_dir;
    var $fix_dir;
    var $version;         // Version of boost to show documentation for.
                          // Empty when not boost.
    var $file_doc_dir;    // Directory to use when checking file system.
    var $url_doc_dir;     // Directory to use in links.
    var $version_title;   // Version string to use in title, or null for default pages.
    var $boost_root;      // Path to root of current version.
    var $path;
    var $use_http_expire_date;

    static function library_documentation_page() {
        return static::documentation_page(array(
            'fix_dir' => BOOST_FIX_DIR,
            'archive_dir' => STATIC_DIR,
            'use_http_expire_date' => true,
        ));
    }

    static function extra_documentation_page() {
        return static::documentation_page(array(
            'boost-root' => '../libs/release/',
        ));
    }

    static function documentation_page($params) {
        $documentation_page = new BoostDocumentation();

        $documentation_page->archive_dir = BoostWebsite::array_get($params, 'archive_dir', STATIC_DIR);
        $documentation_page->fix_dir = BoostWebsite::array_get($params, 'fix_dir');
        $documentation_page->boost_root = BoostWebsite::array_get($params, 'boost-root', '');
        $documentation_page->use_http_expire_date = BoostWebsite::array_get($params, 'use_http_expire_date', false);

        $pattern = BoostWebsite::array_get($params, 'pattern', '@^[/]([^/]+)(?:[/](.*))?$@');

        // Get Archive Location

        if (array_key_exists('PATH_INFO', $_SERVER) &&
                preg_match($pattern, $_SERVER["PATH_INFO"], $path_parts)) {
            if ($path_parts[1] === 'regression') {
                $documentation_page->url_doc_dir = 'regression';
                $documentation_page->file_doc_dir = 'regression';
            }
            else {
                try {
                    $documentation_page->version = BoostVersion::from($path_parts[1]);
                }
                catch(BoostVersion_Exception $e) {
                    BoostWeb::throw_error_404($_SERVER["PATH_INFO"], 'Unable to find version.');
                }
                $documentation_page->file_doc_dir = is_numeric($path_parts[1][0]) ?
                    "boost_{$path_parts[1]}" : $path_parts[1];
                $documentation_page->url_doc_dir = $path_parts[1];
                $documentation_page->version_title = ucwords($documentation_page->version);
            }

            $path = array_key_exists(2, $path_parts) ? $path_parts[2] : null;
        }
        else {
            $documentation_page->version = BoostVersion::current();
            $documentation_page->file_doc_dir = $documentation_page->version->dir();
            $documentation_page->url_doc_dir = 'release';
            $path = null;
        }

        $documentation_page->path = $path;

        return $documentation_page;
    }

    // The root of the documentation on the filesystem.
    function documentation_dir() {
        return "{$this->archive_dir}/{$this->file_doc_dir}";
    }

    function display_from_archive($content_map = array())
    {
        // Set default values

        $file = false;

        if ($this->fix_dir) {
            $fix_path = "{$this->fix_dir}/{$this->file_doc_dir}/{$this->path}";

            if (is_file($fix_path) ||
                (is_dir($fix_path) && is_file("{$fix_path}/index.html")))
            {
                $file = $fix_path;
            }
        }

        if (!$file) {
            $file = "{$this->archive_dir}/{$this->file_doc_dir}/{$this->path}";
        }

        // Only use a permanent redirect for releases (beta or full).

        $redirect_status_code = $this->version &&
            $this->version->is_numbered_release() ? 301 : 302;

        // Calculate expiry date if requested.

        $expires = null;
        if ($this->use_http_expire_date)
        {
            if (!$this->version) {
                $expires = "+1 week";
            }
            else {
                $compare_version = BoostVersion::from($this->version)->
                    compare(BoostVersion::current());
                $expires = $compare_version === -1 ? "+1 year" :
                    ($compare_version === 0 ? "+1 week" : "+1 day");
            }
        }

        // Last modified date

        if (!is_readable($file)) {
            BoostWeb::throw_error_404($file, 'Unable to find file.');
        }

        $last_modified = max(
            strtotime(BOOST_DOCS_MODIFIED_DATE),        // last manual documenation update
            filemtime(dirname(__FILE__).'/../../generated/current_version.txt'),
                                                        // last release)
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
                $this->path = $this->path.$found_file;
            }
            else {
                if (!BoostWeb::http_headers('text/html', $last_modified, $expires))
                    return;

                $display_dir = new BoostDisplayDir($this, $file);
                return $display_dir->display();
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
            if (preg_match($i[1],$this->path))
            {
                if ($i[0]) {
                    $min_version = BoostVersion::from($i[0]);
                    if ($min_version->compare($this->version) > 0) {
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
                BoostWeb::throw_error_404($file, 'No extractor found for filename.');
            } else {
                BoostWeb::throw_error_404($file);
            }
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

                echo_filtered($extractor, $this, $content);
            }
        }
    }
}

//
// Filters
//

function echo_filtered($extractor, $data, $content) {
    $name = "BoostFilter".underscore_to_camel_case($extractor);
    $extractor = new $name($data, $content);
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
        if (!$filter_data->path ||
            ($filter_data->archive_dir && realpath("{$filter_data->archive_dir}/{$current->dir()}/{$filter_data->path}") !== false) ||
            ($filter_data->fix_dir && realpath("{$filter_data->fix_dir}/{$current->dir()}/{$filter_data->path}") !== false))
        {
            echo '<a class="boost-common-header-inner" href="/doc/libs/release/',$filter_data->path,'">',
                "This is the documentation for an old version of Boost.
                Click here to view this page for the latest version.",
                '</a>';
        }
        else
        {
            echo '<a class="boost-common-header-inner" href="/doc/libs/">',
                "This is an the documentation for an old version of boost.
                Click here for the latest Boost documentation.",
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
