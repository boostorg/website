<?php

echo("<ul class=\"toc\">\n");

foreach ($downloads as $x) {
    echo "<li><a href=\"#{$x["anchor"]}\">{$x["label"]}</a></li>\n";
}

echo("<li><a href=\"#history\">Old Boost Releases</a></li>\n");
echo("<li><a href=\"#repository\">Git Repositories</a></li>\n");
echo("</ul>\n");

foreach ($downloads as $x) {
    echo("<h2 id=\"{$x["anchor"]}\">{$x["label"]}</h2>");
    foreach ($x["entries"] as $entry) {
        echo("\n");
        echo("              <h3><span class=\n              \"news-title\">{$entry->full_title_xml}</span></h3>\n\n");
        echo("              <p class=\"news-date\">{$entry->web_date}</p>\n\n");
        echo("              <p class=\"news-description\">\n");
        echo("              <span class=\"brief\"><span class=\"purpose\">{$entry->purpose_xml}</span></span></p>\n\n");
        echo("<ul class=\"menu\">\n");
        echo("<li>");
        echo("<a href=\"/{$entry->location}\">Release Notes</a>");
        echo("</li>\n");
        if ($entry->download_page) {
            echo("<li>");
            echo("<a href=\"".html_encode($entry->download_page)."\">Download</a>");
            echo("</li>\n");
        }
        if ($entry->documentation) {
            echo("<li>");
            echo("<a href=\"".html_encode($entry->documentation)."\">Documentation</a>");
            echo("</li>\n");
        }
        echo("</ul>\n");
        echo "{$entry->download_table}\n";
    }
}
