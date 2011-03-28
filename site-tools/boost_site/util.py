#!/usr/bin/env python
# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

def htmlencode(text):
    return text.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&rt;')

def fragment_to_string(fragment):
    """
    Convert a minidom document fragment to a string.

    Because 'toxml' doesn't work:
    http://bugs.python.org/issue9883
    """
    return ''.join(x.toxml('utf-8').decode('utf-8') for x in fragment.childNodes)
