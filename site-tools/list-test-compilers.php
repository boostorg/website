#!/usr/bin/env php
<?php

# List the test compilers for a new release.

function main() {
    $develop_testers = load_testers('develop');
    $master_testers = load_testers('master');

    echo "[section Compilers Tested]\n";
    echo "\n";
    echo "Boost's primary test compilers are:\n";
    echo "\n";
    echo write_test_compilers($master_testers);
    echo "\n";
    echo "Boost's additional test compilers include:\n";
    echo "\n";
    echo write_test_compilers($develop_testers);
    echo "\n";
    echo "[endsect]\n";
}

function write_test_compilers($test_compilers) {
    // Sort the OS names, putting the main operations systems first.
    $os_list = array_keys($test_compilers);
    $priorities = array();
    foreach($os_list as $os) {
        $os_lower = strtolower($os);
        if ($os_lower == 'windows' || $os_lower == 'linux' || $os_lower == 'os x') {
            $priorities[] = 0;
        } else {
            $priorities[] = 1;
        }
    }
    array_multisort($priorities, $os_list);

    $result = '';

    foreach($os_list as $os) {
        $result .= "* {$os}:\n";

        $compilers = array_keys($test_compilers[$os]);
        sort($compilers);
        foreach($compilers as $compiler) {
            $language_versions = array_keys($test_compilers[$os][$compiler]);
            sort($language_versions);
            foreach($language_versions as $language_version) {
                $compiler_versions = array_keys($test_compilers[$os][$compiler][$language_version]);
                sort($compiler_versions);
                $result .= "  * {$compiler}";
                if ($language_version && $language_version != 'C++03') { $result .= ", {$language_version}"; }
                $result .= ": ";
                $result .= implode(", ", $compiler_versions);
                $result .= "\n";
            }
        }
    }

    return $result;
}

function load_testers($branch) {
    $compiler_conversion = array(
        'GNU C++' => 'GCC',
        'Microsoft Visual C++' => 'Visual C++',
        'Intel C++ C++0x mode' => 'Intel',
        'Intel C++' => 'Intel',
        'Clang' => 'Clang',
    );
    $url_path = "/development/tests/{$branch}/developer/config_.html";
    $test_links = get_test_links($branch, $url_path);

    $test_compilers = array();

    foreach($test_links as $link_details) {
        $test_results = download_test_results(resolve_url_path($link_details['link'], $url_path));
        if (!$test_results) {
            echo "No test results for {$branch}:\n";
            print_r($link_details);
            echo "\n";
            continue;
        }

        foreach($test_results as $test_result) {
            $test_result = preg_replace('@\r\n?@', "\n", $test_result);
            $test_result_parts = explode('*********************************************************************', $test_result);
            $compiler_info = trim($test_result_parts[0]);
            if (!preg_match('@^(.*)version[ \t]*([0-9.]+)(?:[- ]+.*)?$@m', $compiler_info, $match)) {
                throw new RuntimeException("Unable to match compiler + version: $compiler_info");
            }
            $compiler = trim($match[1]);
            if (array_key_exists($compiler, $compiler_conversion)) {
                $compiler = $compiler_conversion[$compiler];
            } else {
                echo "Unknown Compiler: {$compiler}\n";
            }
            $compiler_version = trim($match[2]);
            if (strtolower($compiler) == 'intel' && $compiler_version > 1000) {
                $compiler_version = $compiler_version / 100;
                if (is_int($compiler_version)) { $compiler_version .= ".0"; }
            }
            $variables = substr($compiler_info, strlen($match[0]));
            if (preg_match_all('@^[ \t]*(\w*)[ \t]*=(.*)$@m', $variables, $matches, PREG_SET_ORDER)) {
                $variables = array();
                foreach ($matches as $match) {
                    $variables[$match[1]] = $match[2];
                }

                if (array_key_exists('__QNX__', $variables)) {
                    $compiler = 'QCC';
                }
                $language_version = false;
                if (array_key_exists('__cplusplus', $variables)) {
                    if (preg_match('@^(\d{6})L?$@', $variables['__cplusplus'], $match)) {
                        if ($match[1] < 201103) {
                            $language_version = 'C++03';
                        } else if ($match[1] < 201402) {
                            $language_version = 'C++11';
                        } else if ($match[1] < 201406) {
                            $language_version = 'C++14';
                        } else if ($match[1] < 201703) {
                            $language_version = 'C++1z';
                        } else {
                            $language_version = 'C++17';
                        }
                    }
                }
                if (!$language_version) {
                    if (array_key_exists('__GXX_EXPERIMENTAL_CXX0X__', $variables)) {
                        $language_version = 'C++0x';
                    } else if ($compiler == 'GCC' || $compiler == 'Clang') {
                        $language_version = 'C++03';
                    }
                }
                if (!$language_version) {
                    echo "No language version for: {$compiler}\n";
                    print_r($variables);
                    $language_version = 'C++03';
                }
            }

            $test_compilers[$link_details['os']][$compiler][$language_version][$compiler_version] = true;
        }
    }

    return $test_compilers;
}

function get_test_links($branch, $url_path) {
    $config_summary_dom = DOMDocument::loadHTML(download_page($url_path));
    if (!$config_summary_dom) {
        throw new RuntimeException("Error parsing summary for branch {$branch}");
    }

    $xpath = new DOMXPath($config_summary_dom);

    // Operating System: Android, Darwin, FreeBSD, etc
    $os_columns = array();
    foreach($xpath->query("//table[@class='library-table']/thead/tr[1]/td") as $node) {
        $colspan = $node->hasAttribute('colspan') ? $node->getAttribute('colspan') : 1;
        for ($i = 0; $i < $colspan; ++$i) {
            $os = trim($node->textContent);
            if (strtolower($os) == 'darwin') { $os = 'OS X'; }
            $os_columns[] = $os;
        }
    }

    // Runner: CrystaX-apilevel-19-armeabi-v7a-gnu-libstdc++, teeks99-02-dc3.5-14-Docker-64on64
    $runner_columns = array();
    foreach($xpath->query("//table[@class='library-table']/thead/tr[2]/td") as $node) {
        $colspan = $node->hasAttribute('colspan') ? $node->getAttribute('colspan') : 1;
        for ($i = 0; $i < $colspan; ++$i) {
            $runner_columns[] = trim($node->textContent);
        }
    }

    // Toolset name: clang-gnu-linux-3.6, gcc-gnu-4.9, etc.
    $toolset_columns = array();
    foreach($xpath->query("//table[@class='library-table']/thead/tr[5]/td") as $node) {
        $colspan = $node->hasAttribute('colspan') ? $node->getAttribute('colspan') : 1;
        for ($i = 0; $i < $colspan; ++$i) {
            $toolset_columns[] = preg_replace('@\s+@', '', $node->textContent);
        }
    }

    // config_info links
    $config_info_columns = array();
    $config_info_row = $xpath->query("//table[@class='library-table']/tbody/tr".
        "[td[@class='test-name']/a[text()[normalize-space() = 'config_info']]]");
    if ($config_info_row->length != 1) {
        throw new RuntimeException("Unable to find config_info row");
    }
    foreach($config_info_row->item(0)->getElementsByTagName("td") as $td_node) {
        $a_nodes = $td_node->getElementsByTagName("a");
        if ($a_nodes->length > 1) {
            throw new RuntimeException("Multiple links in config_info cell");
        }
        $link = null;
        if ($a_nodes->length == 1 && trim($a_nodes->item(0)->textContent) == 'pass' && $a_nodes->item(0)->hasAttribute('href')) {
            $link = $a_nodes->item(0)->getAttribute('href');
        }
        $colspan = $td_node->hasAttribute('colspan') ? $td_node->getAttribute('colspan') : 1;
        for ($i = 0; $i < $colspan; ++$i) {
            $config_info_columns[] = $link;
        }
    }

    $test_links = array();
    foreach ($config_info_columns as $index => $link) {
        if ($link && $os_columns[$index]) {
            $test_links[] = array(
                'os' => $os_columns[$index],
                'runner' => $runner_columns[$index],
                'toolset' => $toolset_columns[$index],
                'link' => $link,
            );
        }
    }

    return $test_links;
}

function download_test_results($url_path) {
    $frame_link = get_frame_link_from_frameset($url_path);
    return get_test_results_from_frame(resolve_url_path($frame_link, $url_path));
}

function get_frame_link_from_frameset($url_path) {
    $frame_set = DOMDocument::loadHTML(download_page($url_path));
    if (!$frame_set) {
        throw new RuntimeException("Error downloading test results frame");
    }

    $xpath = new DOMXPath($frame_set);
    $frame_node = $xpath->query("//frame[@name='docframe']");
    if ($frame_node->length != 1) {
        throw new RuntimeException("Error getting docframe");
    }
    return $frame_node->item(0)->getAttribute('src');
}

function get_test_results_from_frame($url_path) {
    $page = DOMDocument::loadHTML(download_page($url_path));
    if (!$page) {
        throw new RuntimeException("Error downloading test results");
    }

    $a_nodes = $page->getElementsByTagName('a');
    if ($a_nodes->length) {
        $pages = array();
        foreach($a_nodes as $a_node) {
            $page = DOMDocument::loadHTML(download_page(
                resolve_url_path($a_node->getAttribute('href'), $url_path)));
            if (!$page) {
                throw new RuntimeException("Error downloading test results");
            }
            $pages[] = $page;
        }
    } else {
        $pages = array($page);
    }

    $results = array();
    foreach ($pages as $page) {
        $xpath = new DOMXPath($page);
        foreach($xpath->query("//div[@class='log-linker-output-title']") as $node) {
            if (preg_match('@^Run \[.*\]: (.*)@', trim($node->textContent), $match)) {
                do { $node = $node->nextSibling; } while($node && $node->nodeType != XML_ELEMENT_NODE);
                if ($node) { $results[] = $node->textContent; }
                break;
            }
        }
    }
    return $results;
}

function download_page($url_path) {
    $cache_path = __DIR__."/cache{$url_path}";
    if (!is_file($cache_path)) {
        $parent_dir = dirname($cache_path);
        if (!is_dir($parent_dir)) { mkdir($parent_dir, 0777, true); }
        $page = file_get_contents("http://www.boost.org{$url_path}");
        if (!$page) {
            throw new RuntimeException("Error downloading: {$url_path}");
        }
        file_put_contents($cache_path, $page);
        return $page;
    } else {
        return file_get_contents($cache_path);
    }
}

function resolve_url_path($url_path, $base) {
    if (strpos($url_path, ':') !== false) {
        throw new RuntimeException("Absolute URL");
    }
    if ($url_path[0] == '/') { return $url_path; }
    $base = preg_replace('@[^/]*$@', '', $base);
    return $base ? $base.$url_path : '/'.$url_path;
}

main();
