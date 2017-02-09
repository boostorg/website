<?php

$news = array_slice($news, 0, 3);

echo("<div class=\"directory-item\" id=\"important-downloads\">\n");
echo("<h2>Downloads</h2>\n");
echo("<div id=\"downloads\">\n");

foreach ($downloads as $x) {
    $label = $x["label"];
    $entries = $x["entries"];
    echo("<h3>{$label}</h3>\n");
    echo("<ul>\n");
    foreach ($entries as $entry) {
        echo("<li>");
        echo("<div class=\"news-title\">");
        echo("<a href=\"/".html_encode($entry->location)."\">".$entry->full_title_xml."</a>");
        echo("</div>");
        echo("<div class=\"news-date\">");
        echo("<a href=\"/".html_encode($entry->location)."\">Release Notes</a>");
        if ($entry->download_page) {
            echo(" | ");
            echo("<a href=\"".html_encode($entry->download_page)."\">Download</a>");
        }
        if ($entry->documentation) {
            echo(" | ");
            echo("<a href=\"".html_encode($entry->documentation)."\">Documentation</a>");
        }
        echo("</div>");
        if ($entry->notice_xml) {
            if ($entry->notice_url) {
                echo("<div class=\"news-notice\"><a class=\"news-notice-link\" href=\"".html_encode($entry->notice_url)."\">{$entry->notice_xml}</a></div>");
            } else {
                echo("<div class=\"news-notice\">{$entry->notice_xml}</div>");
            }
        }
        echo("<div class=\"news-date\">{$entry->web_date}</div>");
        echo("</li>\n");
    }
    echo("</ul>\n");
}

echo("</div>\n");
echo("<p><a href=\"/users/download/\">More Downloads...</a>");
echo(" (<a href=\"feed/downloads.rss\">RSS</a>)</p>\n");
echo("</div>\n\n");

echo("<div class=\"directory-item\" id=\"important-news\">\n");
echo("<h2>News</h2>\n\n");

echo("<ul id=\"news\">\n");

foreach ($news as $entry) {
    echo("\n");
    echo("                    <li><span class=\n");
    echo("                    \"news-title\"><a href=\"/".html_encode($entry->location)."\">{$entry->full_title_xml}</a></span>\n");
    echo("                    <span class=\n");
    echo("                    \"news-description\"><span class=\"brief\"><span class=\"purpose\">{$entry->purpose_xml}</span></span></span>\n");
    echo("                    <span class=\n");
    echo("                    \"news-date\">{$entry->web_date}</span></li>");
}
echo("</ul>\n\n");

echo("<p><a href=\"/users/news/\">More News...</a> (<a href=\"feed/news.rss\">RSS</a>)</p>\n");
echo("</div>\n\n");
