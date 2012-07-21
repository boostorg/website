<?php
    header('Content-Type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
    define('USE_SERIALIZED_INFO', true);
    require_once(dirname(__FILE__) . '/common/code/boost.php');
    require_once(dirname(__FILE__) . '/common/code/boost_libraries.php');
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php

// Returns true if the library is part of the current release of boost.

function current_version_filter($lib) {
	global $boost_current_version;
	return explode('.',$lib['boost-version']) <= $boost_current_version;
}

function xmlentities($text) {
    return str_replace(
        array('&', '<', '>', '"', "'"),
        array('&amp;', '&lt;', '&gt;', '&quot;', '&apos;'),
        $text);
}

function echo_sitemap_url($loc, $priority, $freq) {
    $loc_xml = xmlentities("http://$_SERVER[HTTP_HOST]/$loc");

    echo <<<EOL
<url>
<loc>$loc_xml</loc>
<priority>$priority</priority>
<changefreq>$freq</changefreq>
</url>

EOL;
}

// Library list

echo_sitemap_url("doc/libs/", '1.0', 'daily');

// Library 'home pages'

$libs = USE_SERIALIZED_INFO ?
	unserialize(file_get_contents(dirname(__FILE__) . '/generated/libraries.txt')) :
	new boost_libraries(dirname(__FILE__) . '/doc/libraries.xml');

foreach ($libs->get(null, 'current_version_filter') as $lib) {
    echo_sitemap_url("doc/libs/release/$lib[documentation]", '1.0', 'daily');
}

?>
</urlset>
