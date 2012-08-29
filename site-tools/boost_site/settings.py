# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

settings = {
    'downloads' : [
        {
            'anchor': 'live',
            'single': 'Current Release',
            'plural': 'Current Releases',
            'matches': ['feed/history/*.qbk|released'],
            'count': 1
        },
        {
            'anchor': 'beta',
            'single': 'Beta Release',
            'plural': 'Beta Releases',
            'matches': ['feed/history/*.qbk|beta']
        }
    ],
    'pages': {
        'users/history/': {
            'src_files' : ['feed/history/*.qbk'],
            'type'      : 'release'
        },
        'users/news/': {
            'src_files' : ['feed/news/*.qbk'],
        },
        'users/download/': {
            'src_files' : ['feed/downloads/*.qbk'],
            'type'      : 'release'
        }
    },
    'index-pages' : {
        'generated/download-items.html' : 'site-tools/templates/download-template.py',
        'generated/history-items.html' : 'site-tools/templates/history-template.py',
        'generated/news-items.html' : 'site-tools/templates/news-template.py',
        'generated/home-items.html' : 'site-tools/templates/index-template.py'
    },
    # See boost_site.pages for matches pattern syntax.
    #
    # glob [ '|' flag ]
    'feeds' : {
        'generated/downloads.rss' : {
            'link' : 'users/download/',
            'title': 'Boost Downloads',
            'matches': ['feed/history/*.qbk|released', 'feed/downloads/*.qbk'],
            'count': 3
        },
        'generated/history.rss' : {
            'link' : 'users/history/',
            'title': 'Boost History',
            'matches': ['feed/history/*.qbk|released']
        },
        'generated/news.rss' : {
            'link' : 'users/news/',
            'title': 'Boost News',
            'matches': ['feed/news/*.qbk', 'feed/history/*.qbk|released'],
            'count': 5
        },
        'generated/dev.rss' : {
            'link' : '',
            'title': 'Release notes for work in progress boost',
            'matches': ['feed/history/*.qbk'],
            'count': 5
        }
    }
}
