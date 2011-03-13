#!/usr/bin/env python
# Copyright 2007 Rene Rivera
# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

"""Usage: python build.py [command]

If command is omitted then 'update' is used.

Commands:

update      Update the html pages and rss feeds for new or updated
            quickbook files.

refresh     Reconvert all the quickbook files and regenerate the html
            pages. Does not update the rss feeds or add new pages.
            Useful for when quickbook, the scripts or the templates have
            been updated.

docs        Update the documentation list from doc/libraries.xml.
            Requires php to be on the path and the site to be configured.

start       Setup the state file and regenerate html files from the old
            hashes files.

"""

import os, sys, subprocess, glob, re, time, xml.dom.minidom, codecs
import boost_site.templite, boost_site.pages, boost_site.boostbook_parser, boost_site.util

################################################################################

settings = {
    'pages': {
        'users/history/': {
            'src_files' : ['feed/history/*.qbk'],
            'template' : 'build/templates/entry-template.html'
        },
        'users/news/': {
            'src_files' : ['feed/news/*.qbk'],
            'template' : 'build/templates/entry-template.html'
        },
        'users/download/': {
            'src_files' : ['feed/downloads/*.qbk'],
            'template' : 'build/templates/entry-template.html'
        }
    },
    'index-pages' : {
        'users/download/index.html' : 'build/templates/download-template.html',
        'users/history/index.html' : 'build/templates/history-template.html',
        'users/news/index.html' : 'build/templates/news-template.html',
        'index.html' : 'build/templates/index-src.html'
    },
    'feeds' : {
        'feed/downloads.rss' : {
            'title': 'Boost Downloads',
            'matches': ['feed/history/*.qbk', 'feed/downloads/*.qbk'],
            'count': 5
        },
        'feed/history.rss' : {
            'title': 'Boost History',
            'matches': ['feed/history/*.qbk']
        },
        'feed/news.rss' : {
            'title': 'Boost News',
            'matches': ['feed/news/*.qbk', 'feed/history/*.qbk'],
            'count': 5
        }
    }
}

################################################################################

def main(argv):
    os.chdir(os.path.join(os.path.dirname(sys.argv[0]), "../"))

    if len(argv) > 1:
        print __doc__
        return

    if len(argv) == 1 and argv[0]:
        command = argv[0]
    else:
        command = 'update'

    if command == 'docs':
        return update_php_docs()
    elif command == 'update':
        return update_quickbook(False)
    elif command == 'refresh':
        return update_quickbook(True)
    elif command == 'start':
        status = convert_hash_files()
        if(status != 0): return status
        return update_quickbook(True)
    else:
        print __doc__
        return

def update_php_docs():
    try:
        subprocess.check_call(['php', 'build/build.php'])
    except:
        print "PHP documentation serialization failed."

def convert_hash_files():
    hashes = {}

    for hash_file in glob.glob('feed/*-hashes.txt'):
        new_hashes = load_hashes(hash_file)

        for qbk_file in new_hashes:
            full_path = 'feed/%s' % qbk_file
            if(full_path in hashes and hashes[full_path] != new_hashes[qbk_file]):
                print "Contradiction for %s" % qbk_file
                return -1
            else:
                hashes[full_path] = new_hashes[qbk_file]

    state = {}

    for location in settings['pages']:
        pages_data = settings['pages'][location]
        for src_file_pattern in pages_data['src_files']:
            for qbk_file in glob.glob(src_file_pattern):
                if qbk_file in hashes:
                    state = hashes[qbk_file]
                    state['dir_location'] = location

    boost_site.state.save(hashes, 'build/state/feed-pages.txt')
    return 0

def load_hashes(hash_file):
    qbk_hashes = {}

    file = open(hash_file)
    try:
        for line in file:
            (qbk_file, qbk_hash, rss_hash) = line.strip().split(',')
            qbk_hashes[qbk_file] = {'qbk_hash': qbk_hash, 'rss_hash': rss_hash}
        return qbk_hashes
    finally:
        file.close()        

def update_quickbook(refresh):
    # Now check quickbook files.
    
    pages = boost_site.pages.Pages('build/state/feed-pages.txt')

    if not refresh:
        for location in settings['pages']:
            pages_data = settings['pages'][location]
            for src_file_pattern in pages_data['src_files']:
                for qbk_file in glob.glob(src_file_pattern):
                    pages.add_qbk_file(qbk_file, location)

        pages.save()
    
    # Translate new and changed pages

    pages.convert_quickbook_pages(refresh)

    # Generate 'Index' pages

    for index_page in settings['index-pages']:
        boost_site.templite.write_template(
            index_page,
            settings['index-pages'][index_page],
            { 'pages' : pages })

    # Generate RSS feeds

    if not refresh:
        for feed_file in settings['feeds']:
            feed_data = settings['feeds'][feed_file]
            rss_feed = generate_rss_feed(feed_file, feed_data)
            rss_channel = rss_feed.getElementsByTagName('channel')[0]
            old_rss_items = pages.load_rss(feed_file, rss_feed)
            
            feed_pages = pages.match_pages(feed_data['matches'])
            if 'count' in feed_data:
                feed_pages = feed_pages[:feed_data['count']]
            
            for qbk_page in feed_pages:
                if qbk_page.loaded:
                    item = generate_rss_item(rss_feed, qbk_page.qbk_file, qbk_page)
                    pages.add_rss_item(item)
                    rss_channel.appendChild(item['item'])
                elif qbk_page.qbk_file in old_rss_items:
                    rss_channel.appendChild(old_rss_items[qbk_page.qbk_file]['item'])
                else:
                    print "Missing entry for %s" % qbk_page.qbk_file
                    
            output_file = open(feed_file, 'w')
            try:
                output_file.write(rss_feed.toxml('utf-8'))
            finally:
                output_file.close()

    pages.save()

################################################################################

def generate_rss_feed(feed_file, details):
    rss = xml.dom.minidom.parseString('''<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:boostbook="urn:boost.org:boostbook">
  <channel>
    <generator>BoostBook2RSS</generator>
    <title>%(title)s</title>
    <link>%(link)s</link>
    <description>%(description)s</description>
    <language>%(language)s</language>
    <copyright>%(copyright)s</copyright>
  </channel>
</rss>
''' % {
    'title' : details['title'],
    'link' : "http://www.boost.org/" + feed_file,
    'description' : '',
    'language' : 'en-us',
    'copyright' : 'Distributed under the Boost Software License, Version 1.0. (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)'
    } )

    return rss

def generate_rss_item(rss_feed, qbk_file, page):
    assert page.loaded

    item = rss_feed.createElement('item')

    title = xml.dom.minidom.parseString('<title>%s</title>' % page.title_xml)
    item.appendChild(rss_feed.importNode(title.documentElement, True))

    # TODO: Convert date format?
    node = rss_feed.createElement('pubDate')
    node.appendChild(rss_feed.createTextNode(page.pub_date))
    item.appendChild(node)
    
    node = rss_feed.createElement('boostbook:purpose')
    node.appendChild(rss_feed.createTextNode(page.purpose_xml))
    item.appendChild(node)

    if page.download_item:
        node = rss_feed.createElement('boostbook:downlaod')
        node.appendChild(rss_feed.createTextNode(page.download_item))
        item.appendChild(node)

    node = rss_feed.createElement('description')
    node.appendChild(rss_feed.createTextNode(page.description_xml))
    item.appendChild(node)

    return({
        'item': item,
        'quickbook': qbk_file,
        'last_modified': page.last_modified
    })

################################################################################

if __name__ == "__main__":
    main(sys.argv[1:])