from boost_site.util import htmlencode

# TODO: This is duplicated from other places, should only be set once?
news = pages.match_pages(['feed/news/*.qbk', 'feed/history/*.qbk|released'], 3)

emit('<div class="directory-item" id="important-downloads">\n');
emit('<h2>Downloads</h2>\n');
emit('<div id="downloads">\n');

for x in downloads:
    label = x['label']
    entries = x['entries']
    emit('<h3>%s</h3>\n' % label)
    emit('<ul>\n')
    for entry in entries:
        emit('<li>')
        emit('<div class="news-title">')
        if entry.download_item:
            emit('<a href="%s">' % htmlencode(entry.download_item))
        emit(entry.full_title_xml)
        if entry.download_item:
            emit('</a>')
        emit('</div>')
        emit('<div class="news-date">')
        emit('<a href="/%s">Release Notes</a>' % (htmlencode(entry.location)))
        if entry.documentation:
            emit(' | ')
            emit('<a href="%s">Documentation</a>' % (htmlencode(entry.documentation)))
        emit('</div>')
        emit('<div class="news-date">%s</div>' % (entry.web_date()))
        emit('</li>\n')
    emit('</ul>\n')

emit('</div>\n')
emit('<p><a href="/users/download/">More Downloads...</a>')
emit('(<a href="feed/downloads.rss">RSS</a>)</p>\n')
emit('</div>\n\n')

emit('<div class="directory-item" id="important-news">\n')
emit('<h2>News</h2>\n\n')

emit('<ul id="news">\n')

for entry in news:
    emit('\n')
    emit('                    <li><span class=\n                    "news-title"><a href="/%s">%s</a></span>\n' % (htmlencode(entry.location), entry.full_title_xml))
    emit('                    <span class=\n                    "news-description"><span class="brief"><span class="purpose">%s</span></span></span>\n' % entry.purpose_xml)
    emit('                    <span class=\n                    "news-date">%s</span></li>' % (entry.web_date()))
emit('</ul>\n\n')

emit('<p><a href="/users/news/">More News...</a> (<a href=feed/news.rss">RSS</a>)</p>\n')
emit('</div>\n\n')

emit('<div class="clear"></div>\n')
