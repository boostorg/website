<?php

$title = "Boost Version History";

foreach ($released_versions as $entry) {
    echo("\n");
    echo("              <h2 class=\"news-title\">\n");
    echo("              <a name=\"i{$entry->id}\" id=\"i{$entry->id}\"></a><a href=\"/".
        html_encode($entry->location).
        "\">{$entry->title_xml}</a></h2>\n\n");
    echo("              <p class=\"news-date\">".$entry->web_date()."</p>\n\n");
    echo("              <div class=\"news-description\">\n");
    echo("                <span class=\"brief\"><span class=\"purpose\">{$entry->purpose_xml}</span></span>\n");
    echo("              </div>\n\n");
    echo("<ul class=\"menu\">\n");
    echo("<li>");
    echo("<a href=\"/".html_encode($entry->location)."\">Release Notes</a>");
    echo("</li>\n");
    if ($entry->download_item) {
        echo("<li>");
        echo("<a href=\"".html_encode($entry->download_item)."\">Download</a>");
        echo("</li>\n");
    }
    if ($entry->documentation) {
        echo("<li>");
        echo("<a href=\"".html_encode($entry->documentation)."\">Documentation</a>");
        echo("</li>\n");
    }
    echo("</ul>\n");
}
