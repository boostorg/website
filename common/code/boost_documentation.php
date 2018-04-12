<?php
/*
  Copyright 2005-2008 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or https://www.boost.org/LICENSE_1_0.txt)
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

    static function library_documentation_page() {
        return static::documentation_page(array(
            'fix_dir' => BOOST_FIX_DIR,
            'archive_dir' => STATIC_DIR,
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

    // Call to redirect if appropriate before rendering the page
    function redirect_if_appropriate()
    {
        if ($this->version && $this->version->is_beta() &&
            BoostVersion::current()->compare($this->version) > 0)
        {
            header("Location: /doc/libs/{$this->version->final_doc_dir()}/{$this->path}");
            return true;
        }
        return false;
    }

    function display_from_archive($content_map = array())
    {
        if ($this->redirect_if_appropriate()) {
            return;
        }

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

        if (!is_readable($file)) {
            $tmp_file = "{$this->fix_dir}/fallback/{$this->path}";
            if (is_readable($tmp_file)) {
                $file = $tmp_file;
            } else {
                BoostWeb::throw_error_404($file, 'Unable to find file.');
            }
        }

        // Only use a permanent redirect for releases (beta or full).

        $redirect_status_code = $this->version &&
            $this->version->is_numbered_release() ? 301 : 302;

        // Calculate expiry date if requested.

        if (!$this->version) {
            $expires = "+1 week";
        }
        else {
            $compare_version = BoostVersion::from($this->version)->
                compare(BoostVersion::current());
            $expires = $compare_version === -1 ? "+1 month" :
                ($compare_version === 0 ? "+1 week" : "+5 minutes");
        }

        // Last modified date


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
                if (!BoostWeb::http_headers('text/html', null, $expires))
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

        if($extractor == 'raw' || BoostWebsite::array_get($_GET, 'format') == 'raw') {
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
                    BoostWeb::http_headers('text/html', null, "+5 minutes");
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
            '@<meta\s+http-equiv\s*=\s*["\']?refresh["\']?\s+content\s*=\s*["\']0;\s*URL=([^"\']*)["\']\s*/?\>@i',
            $content, $redirect))
    {
        return BoostUrl::resolve($redirect[1]);
    }

    return false;
}

function latest_link($filter_data)
{
    $result = '';

    if (!isset($filter_data->version)) {
        return $result;
    }

    $version = BoostVersion::from($filter_data->version);

    $current = BoostVersion::current();
    switch ($current->compare($version))
    {
    case 0:
        break;
    case 1:
        $result .= '<div class="boost-common-header-notice">';
        if (!$filter_data->path ||
            ($filter_data->archive_dir && realpath("{$filter_data->archive_dir}/{$current->dir()}/{$filter_data->path}") !== false) ||
            ($filter_data->fix_dir && realpath("{$filter_data->fix_dir}/{$current->dir()}/{$filter_data->path}") !== false) ||
            ($filter_data->fix_dir && realpath("{$filter_data->fix_dir}/fallback/{$filter_data->path}") !== false))
        {
            $result .= '<a class="boost-common-header-inner" href="/doc/libs/release/'.$filter_data->path.'">'.
                "This is the documentation for an old version of Boost.
                Click here to view this page for the latest version.".
                '</a>';
        }
        else
        {
            $result .= '<a class="boost-common-header-inner" href="/doc/libs/">'.
                "This is the documentation for an old version of boost.
                Click here for the latest Boost documentation.".
                '</a>';
        }
        $result .= '</div>'. "\n";
        break;
    case -1:
        if ($version->release_stage() === BoostVersion::release_stage_development) {
            $hash_path = realpath("{$filter_data->documentation_dir()}/.bintray-version");
            $hash = $hash_path ? trim(file_get_contents($hash_path)) : null;
            if (is_string($hash) && $hash[0] == '{') {
                $hash = json_decode($hash);
                $hash = $hash->hash;
            }

            $result .= '<div class="boost-common-header-notice">';
            $result .= '<span class="boost-common-header-inner">';
            $result .= "This is the documentation for a snapshot of the {$version} branch";
            if ($hash) {
                $result .= ", built from commit <a href='";
                $result .= "https://github.com/boostorg/boost/commit/{$hash}";
                $result .= "'>";
                $result .= substr($hash, 0, 10);
                $result .= "</a>";
            }
            $result .= ".";
            $result .= '</span>';
            $result .= '</div>'. "\n";
        }
        else {
            $result .= '<div class="boost-common-header-notice">';
            $result .= '<span class="boost-common-header-inner">';
            $result .= 'This is the documentation for a development version of boost.';
            $result .= '</span>';
            $result .= '</div>'. "\n";
        }
        break;
    }

    return $result;
}
