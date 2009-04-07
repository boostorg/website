#!/usr/bin/python
# Copyright 2007 Rene Rivera
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

import re
import optparse
import time
import xml.dom.minidom
from xml.sax.saxutils import unescape, escape

class BoostBook2RSS:

    def __init__(self):
        opt = optparse.OptionParser(
            usage="%prog [options] input+")
        opt.add_option( '--output',
            help="output RSS file" )
        opt.add_option( '--channel-title' )
        opt.add_option( '--channel-link' )
        opt.add_option( '--channel-language' )
        opt.add_option( '--channel-copyright' )
        opt.add_option( '--channel-description' )
        opt.add_option( '--count', type='int' )
        self.output = 'out.rss'
        self.channel_title = ''
        self.channel_link = ''
        self.channel_language = 'en-us'
        self.channel_copyright = 'Distributed under the Boost Software License, Version 1.0. (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)'
        self.channel_description = ''
        self.count = None
        self.input = []
        ( _opt_, self.input ) = opt.parse_args(None,self)
        self.rss = xml.dom.minidom.parseString('''<?xml version="1.0" encoding="UTF-8"?>
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
            'title' : self.channel_title,
            'link' : self.channel_link,
            'description' : self.channel_description,
            'language' : self.channel_language,
            'copyright' : self.channel_copyright
            } )
        
        self.add_articles()
        self.gen_output()
    
    def add_articles(self):
        channel = self.get_child(self.rss.documentElement,tag='channel')
        items = []
        for bb in self.input:
            article = xml.dom.minidom.parse(bb)
            item = self.x(article.documentElement)
            if item:
                try:
                    items.append([
                        time.mktime(time.strptime(
                            article.documentElement.getAttribute('last-revision'),
                            '%a, %d %b %Y %H:%M:%S %Z')),
                        item
                        ])
                except:
                    items.append([time.time(),item])
        items.sort(lambda x,y: -cmp(x[0],y[0]))
        for item in items[0:self.count]:
            channel.appendChild(item[1])
    
    def gen_output(self):
        if self.output:
            out = open(self.output,'w')
        else:
            out = sys.stdout
        if out:
            self.rss.writexml(out,encoding='utf-8')
    
    #~ Turns the internal XML tree into an output UTF-8 string.
    def tostring(self):
        #~ return self.boostbook.toprettyxml('  ')
        return self.rss.toxml('utf-8')
    
    def x(self, *context, **kwargs):
        node = None
        names = [ ]
        for c in context:
            if c:
                if not isinstance(c,xml.dom.Node):
                    suffix = '_'+c.replace('-','_').replace('#','_')
                else:
                    suffix = '_'+c.nodeName.replace('-','_').replace('#','_')
                    node = c
                names.append('x')
                names = map(lambda x: x+suffix,names)
        if node:
            for name in names:
                if hasattr(self,name):
                    return getattr(self,name)(node,**kwargs)
                else:
                    assert False, 'Unknown node type %s'%(name)
        return None
    
    def x_children( self, parent, **kwargs ):
        result = []
        for n in parent.childNodes:
            child = self.x(n)
            if child:
                result.append(child)
            else:
                child = n.cloneNode(False)
                if hasattr(child,'data'):
                    child.data = re.sub(r'\s+',' ',child.data)
                for grandchild in self.x_children(n,**kwargs):
                    child.appendChild(grandchild)
        return result
    
    def x_article(self,node):
        brief_xhtml = self.new_node('span',
            self.x(self.get_child(self.get_child(node,tag='articleinfo'),
                tag='articlepurpose'
                )),
            klass='brief'
            )

        title_xhtml = self.new_node('title',
            *self.x_children(self.get_child(node,tag='title')))

        description_xhtml = self.new_node('div',klass='description')
        download_item = None
        body_item = node.firstChild
        while body_item:
            if body_item.nodeName not in ['title', 'articleinfo']:
                item = self.x(body_item)
                if item:
                    download_i = self.get_child(item,tag='boostbook:download')
                    if download_i:
                        download_item = download_i
                    else:
                        description_xhtml.appendChild(item)
            body_item = body_item.nextSibling
        return self.new_node(
            'item',
            title_xhtml,
            self.new_text('pubDate',node.getAttribute('last-revision')),
            self.new_text('boostbook:purpose',brief_xhtml.toxml('utf-8')),
            download_item,
            self.new_text('description',description_xhtml.toxml('utf-8'))
            )
    
    def x__text(self,node):
        return self.rss.createTextNode(node.data);
    
    def x_para(self,node):
        return self.new_node('p',
            *self.x_children(node))
        
    def x_ulink(self,node):
        return self.new_node('a',
            href=node.getAttribute('url'),
            *self.x_children(node))
    
    def x_section(self,node):
        return self.new_node('div',
            id=node.getAttribute('id'),
            *self.x_children(node))
    
    def x_title(self,node):
        return self.new_node('h3',
            *self.x_children(node))
    
    def x_link(self,node):
        return self.new_node('span',
            klass='link',
            *self.x_children(node))
    
    def x_itemizedlist(self,node):
        return self.new_node('ul',
            *self.x_children(node))
    
    def x_listitem(self,node):
        return self.new_node('li',
            *self.x_children(node))
    
    def x_phrase(self,node):
        return self.new_node('span',
            klass=node.getAttribute('role'),
            *self.x_children(node))
    
    def x_code(self,node):
        return self.new_node('code',
            *self.x_children(node))
    
    def x_literal(self,node):
        return self.new_node('tt',
            *self.x_children(node))

    def x_emphasis(self,node):
        return self.new_node('em',
            *self.x_children(node))

    def x_inlinemediaobject(self,node):
        image = self.get_child(node,'imageobject')
        if image:
            image = self.get_child(image,'imagedata')
            if image:
                image = image.getAttribute('fileref')
        alt = self.get_child(node,'textobject')
        if alt:
            alt = self.get_child(alt,'phrase')
            if alt and alt.getAttribute('role') == 'alt':
                alt = self.get_child(alt).data.strip()
            else:
                alt = None
        if not alt:
            alt = '[]'
        if image:
            return self.new_node('img',
                src=image,
                alt=alt)
        else:
            return None
    
    def x_articlepurpose(self,node):
        return self.new_node('span',
            klass='purpose',
            *self.x_children(node))
    
    def x_download(self,node):
        return self.new_text('boostbook:download',
            self.get_child(node).data)
    
    def get_child( self, root, tag = None, id = None, name = None):
        for n in root.childNodes:
            found = True
            if tag and found:
                found = found and tag == n.nodeName
            if id and found:
                if n.hasAttribute('id'):
                    found = found and n.getAttribute('id') == id
                else:
                    found = found and n.hasAttribute('id') and n.getAttribute('id') == id
            if name and found:
                found = found and n.hasAttribute('name') and n.getAttribute('name') == name
            if found:
                return n
        return None
    
    def new_node( self, tag, *child, **kwargs ):
        result = self.rss.createElement(tag)
        for k in kwargs.keys():
            if kwargs[k] != '':
                if k == 'id':
                    result.setAttribute('id',kwargs[k])
                elif k == 'klass':
                    result.setAttribute('class',kwargs[k])
                else:
                    result.setAttribute(k,kwargs[k])
        for c in child:
            if c:
                result.appendChild(c)
        return result
    
    def new_text( self, tag, data, **kwargs ):
        result = self.new_node(tag,**kwargs)
        data = data.strip()
        if len(data) > 0:
            result.appendChild(self.rss.createTextNode(data))
        return result


BoostBook2RSS()
