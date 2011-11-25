<?php
    header('Content-Type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
    define('USE_SERIALIZED_INFO', true);
    require_once(dirname(__FILE__) . '/common/code/boost_libraries.php');
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php

function xmlentities($text) {
    return str_replace(
        array('&', '<', '>', '"', "'"),
        array('&amp;', '&lt;', '&gt;', '&quot;', '&apos;'),
        $text);
}

$libs = USE_SERIALIZED_INFO ?
	unserialize(file_get_contents(dirname(__FILE__) . '/doc/libraries.txt')) :
	new boost_libraries(dirname(__FILE__) . '/doc/libraries.xml');

$base_url = "http://$_SERVER[HTTP_HOST]/doc/libs/release";

foreach ($libs->get() as $lib) {
    $loc_xml = xmlentities($lib['documentation']);
    echo <<<EOL
<url>
<loc>$base_url/$loc_xml</loc>
<priority>1.0</priority>
</url>

EOL;
}

?>
</urlset>