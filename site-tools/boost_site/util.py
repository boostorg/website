#!/usr/bin/env python
# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

import urlparse, re

def htmlencode(text):
    return text.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&rt;')

def fragment_to_string(fragment):
    """
    Convert a minidom document fragment to a string.

    Because 'toxml' doesn't work:
    http://bugs.python.org/issue9883
    """
    x = ''.join(x.toxml('utf-8').decode('utf-8') for x in fragment.childNodes)
    x = re.sub(r' +$', '', x, flags = re.M)
    return x

def base_links(node, base_link):
    transform_links(node, lambda x: urlparse.urljoin(base_link,x))

def transform_links(node, func):
    transform_links_impl(node, 'a', 'href', func)
    transform_links_impl(node, 'img', 'src', func)

def transform_links_impl(node, tag_name, attribute, func):
    if node.nodeType == node.ELEMENT_NODE or \
            node.nodeType == node.DOCUMENT_NODE:
        for x in node.getElementsByTagName(tag_name):
            x.setAttribute(attribute, func(x.getAttribute(attribute)))
    elif node.nodeType == node.DOCUMENT_FRAGMENT_NODE:
        for x in node.childNodes:
            transform_links_impl(x, tag_name, attribute, func)
