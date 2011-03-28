#!/usr/bin/env python
# Copyright 2007 Rene Rivera
# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

import xml.dom.minidom, time
from email.utils import parsedate_tz

class BoostBookParser:
    def __init__(self, document = None):
        if document:
            self.document = document
        else:
            self.document = xml.dom.minidom.getDOMImplementation().createDocument(None, "body", None)
    
    def parse(self, filename):
        article = xml.dom.minidom.parse(filename)        

        article_node = article.documentElement
        if article_node.nodeName != 'article':
            print "Boostbook file not article:", filename
            return

        brief_xhtml = self.new_fragment(
            *self.x_children(article_node.getElementsByTagName('articlepurpose')[0])
        )

        title_xhtml = self.new_fragment(
            *self.x_children(article_node.getElementsByTagName('title')[0])
        )

        download_item = None
        download_node = article_node.getElementsByTagName('download')
        if download_node:
            download_item = self.get_child(download_node[0]).data

        pub_date = article_node.getAttribute('last-revision').strip()

        if not pub_date or pub_date[0] == '$':
            pub_date = 'In Progress'
            last_modified = time.time()
        else:
            last_modified = parsedate_tz(pub_date)
            last_modified = time.mktime(last_modified[:-1]) - last_modified[-1]

        description_xhtml = self.x(article_node)
        
        return {
            'title_fragment' : title_xhtml,
            'purpose_fragment' : brief_xhtml,
            'description_fragment' : description_xhtml,
            'pub_date' : pub_date,
            'last_modified' : last_modified,
            'download_item' : download_item
        }

    def x(self, node):
        "Call conversion method for node"

        # This used to deal with multiple arguments and kwargs, but I didn't
        # understand what it was doing, and it never used it, so I simplified
        # it to just deal with a single node.

        name = 'x_'+node.nodeName.replace('-','_').replace('#','_')

        if hasattr(self,name):
            return getattr(self,name)(node)
        else:
            assert False, 'Unknown node type %s'%(name)
            return None
    
    def x_children( self, parent, **kwargs ):
        result = []
        for n in parent.childNodes:
            result.append(self.x(n))
        return result
    
    def x_article(self,node):
        description_xhtml = self.new_fragment()
        for body_item in node.childNodes:
            if body_item.nodeName in ['title', 'articleinfo']:
                continue
            if self.get_child(body_item, tag = 'download'):
                continue
            description_xhtml.appendChild(self.x(body_item))

        return description_xhtml
    
    def x__text(self,node):
        return self.document.createTextNode(node.data);
    
    def x_para(self,node):
        return self.new_node('p',
            *self.x_children(node))

    def x_simpara(self,node):
        return self.new_node('div',
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

    def new_fragment( self, *child ):
        result = self.document.createDocumentFragment()
        for c in child:
            if c:
                result.appendChild(c)
        return result
    
    def new_node( self, tag, *child, **kwargs ):
        result = self.document.createElement(tag)
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
            result.appendChild(self.document.createTextNode(data))
        return result
