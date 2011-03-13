#!/usr/bin/env python
# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

import boost_site.state, boost_site.util
import os, hashlib, xml.dom.minidom, re, fnmatch, subprocess, tempfile, time

class Pages:
    """ Tracks which items in an rss feed have been updated.

    Stores meta data about the quickbook file, including the signature
    of the quickbook file and the rss item.
    """
    def __init__(self, hash_file):
        self.hash_file = hash_file
    
        # Map of quickbook filename to Page
        self.pages = {}
        
        # Map of rss hash to quickbook filename
        self.rss_hashes = {}

        if(os.path.isfile(hash_file)):
            hashes = boost_site.state.load(hash_file)
            for qbk_file in hashes:
                record = hashes[qbk_file]
                self.pages[qbk_file] = Page(qbk_file, record)
                if(record.get('rss_hash')):
                    self.rss_hashes[record['rss_hash']] = qbk_file

    def save(self):
        save_hashes = {}
        for x in self.pages:
            save_hashes[x] = self.pages[x].state()
        boost_site.state.save(save_hashes, self.hash_file)

    def add_qbk_file(self, qbk_file, location):
        file = open(qbk_file)
        try:
            qbk_hash = hashlib.sha256(file.read()).hexdigest()
        finally:
            file.close()

        record = None

        if qbk_file not in self.pages:
            self.pages[qbk_file] = record = \
                Page(qbk_file)
        else:
            record = self.pages[qbk_file]
            if record.dir_location:
                assert record.dir_location == location
            if record.qbk_hash == qbk_hash:
                return
            if record.page_state != 'new':
                record.page_state = 'changed'

        record.qbk_hash = qbk_hash
        record.dir_location = location

    # You might be wondering why I didn't just save the rss items - would
    # be able to save all the items not just the ones in the feed.
    # I mostly wanted to minimise the amount of stuff that was checked in
    # to subversion with each change.
    def load_rss(self, rss_file, xml_doc):
        rss_items = {}
    
        if(os.path.isfile(rss_file)):
            rss = xml.dom.minidom.parse(rss_file)
            for item in rss.getElementsByTagName('item'):
                hashed = hash_dom_node(item)
                if hashed in self.rss_hashes:
                    rss_items[self.rss_hashes[hashed]] = {
                        'quickbook': self.rss_hashes[hashed],
                        'item': xml_doc.importNode(item, True),
                        'last_modified': self.pages[self.rss_hashes[hashed]].last_modified
                    }
                else:
                    print "Unable to find quickbook file for rss item:"
                    print hashed

        return rss_items

    def add_rss_item(self, item):
        self.pages[item['quickbook']].rss_hash = hash_dom_node(item['item'])

    def convert_quickbook_pages(self, refresh = False):
        try:
            subprocess.check_call(['quickbook', '--version'])
        except:
            print "Problem running quickbook, will not convert quickbook articles."
            return
        
        bb_parser = boost_site.boostbook_parser.BoostBookParser()
    
        for page in self.pages:
            page_data = self.pages[page]
            if page_data.page_state or refresh:
                xml_file = tempfile.mkstemp('', '.xml')
                os.close(xml_file[0])
                xml_filename = xml_file[1]
                try:
                    print "Converting " + page + ":"
                    subprocess.check_call(['quickbook', '--output-file', xml_filename, '-I', 'feed', page])
                    page_data.load(bb_parser.parse(xml_filename), refresh)
                finally:
                    os.unlink(xml_filename)
    
                boost_site.templite.write_template(page_data.location,
                    'build/templates/entry-template.html',
                    { 'page': page_data })


    def match_pages(self, patterns, count = None):
        filtered = set()
        for pattern in patterns:
            filtered = filtered | set(fnmatch.filter(self.pages.keys(), pattern))

        entries = [self.pages[x] for x in filtered if self.pages[x].page_state != 'new']
        entries = sorted(entries, key = lambda x: x.last_modified, reverse=True)
        if count:
            entries = entries[:count]
        return entries

def hash_dom_node(node):
    return hashlib.sha256(node.toxml('utf-8')).hexdigest()

class Page:
    def __init__(self, qbk_file, attrs = None):
        self.qbk_file = qbk_file

        if not attrs: attrs = { 'page_state' : 'new' }

        self.page_state = attrs.get('page_state', None)
        self.dir_location = attrs.get('dir_location', None)
        self.location = attrs.get('location', None)
        self.id = attrs.get('id', None)
        self.title_xml = attrs.get('title', None)
        self.purpose_xml = attrs.get('purpose', None)
        self.last_modified = attrs.get('last_modified')
        self.pub_date = attrs.get('pub_date')
        self.download_item = attrs.get('download')
        self.qbk_hash = attrs.get('qbk_hash')
        self.rss_hash = attrs.get('rss_hash')

        self.loaded = False

    def state(self):
        return {
            'page_state': self.page_state,
            'dir_location': self.dir_location,
            'location': self.location,
            'id' : self.id,
            'title': self.title_xml,
            'purpose': self.purpose_xml,
            'last_modified': self.last_modified,
            'pub_date': self.pub_date,
            'download': self.download_item,
            'qbk_hash': self.qbk_hash,
            'rss_hash': self.rss_hash
        }

    def load(self, values, refresh = False):
        assert self.dir_location or refresh
        assert not self.loaded
    
        self.title_xml = boost_site.util.fragment_to_string(values['title_fragment'])
        self.purpose_xml = boost_site.util.fragment_to_string(values['purpose_fragment'])
        self.description_xml = boost_site.util.fragment_to_string(values['description_fragment'])

        self.pub_date = values['pub_date']
        self.last_modified = values['last_modified']
        self.download_item = values['download_item']
        self.id = re.sub('[\W]', '_', self.title_xml).lower()
        if self.dir_location:
            self.location = self.dir_location + self.id + '.html'
            self.dir_location = None
            self.page_state = None
        
        self.loaded = True

    def web_date(self):
        if self.pub_date == 'In Progress':
            return self.pub_date
        else:
            return time.strftime('%B %e, %Y %H:%M GMT', time.gmtime(self.last_modified))

    def download_table(self):
        if(not self.download_item):
            return ''
    
        match = re.match('.*/boost/(\d+)\.(\d+)\.(\d+)/', self.download_item)
        if(match):
            major = int(match.group(1))
            minor = int(match.group(2))
            point = int(match.group(3))
            base_name = 'boost_' + match.group(1) + '_' + match.group(2) + '_' + match.group(3)
    
            # Pick which files are available by examining the version number.
            # This could possibly be meta-data in the rss feed instead of being
            # hardcoded here.
    
            # TODO: Key order hardcoded later.
            
            downloads = {
                'unix' : [base_name + '.tar.bz2', base_name + '.tar.gz'],
                'windows' : []
            }
    
            if(major == 1 and minor >= 32 and minor <= 33):
                downloads['windows'].append(base_name + '.exe')
            elif(major > 1 or minor > 34 or (minor == 34 and point == 1)):
                downloads['windows'].append(base_name + '.7z')
            downloads['windows'].append(base_name + '.zip')
            
            # Print the download table.
            
            output = ''
            output = output + '<table class="download-table">'
            output = output + '<caption>Downloads</caption>'
            output = output + '<tr><th scope="col">Platform</th><th scope="col">File</th></tr>'
    
            for platform in ['unix', 'windows']:
                files = downloads[platform]
                output += "\n"
                output += '<tr><th scope="row"'
                if(len(files) > 1):
                    output += ' rowspan="' + str(len(files)) + '"';
                output += '>' + boost_site.util.htmlencode(platform) + '</th>'
                output += '</tr><tr>'.join(
                    '<td><a href="' + boost_site.util.htmlencode(self.download_item + file + '/download') + '">' + boost_site.util.htmlencode(file) + '</a></td>'
                    for file in files
                )
                output += '</tr>'
    
            output += '</table>'
            return output
        else:
            # If the link didn't match the normal version number pattern
            # then just use the old fashioned link to sourceforge. */
    
            return '<p><span class="news-download"><a href="' + \
                boost_site.util.htmlencode(self.download_item) + \
                '">Download this release.</a></span></p>';
