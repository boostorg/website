#!/usr/bin/env python
# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

"""Usage: python release.py release_notes.qbk

Mark the supplied release notes as released, and update accordingly.
"""

import sys, os.path
import boost_site.site_tools

if len(sys.argv) != 2:
    print __doc__
    exit(1)

# Because I'm an idiot, we need to get the real path to the quickbook file
# before calling init.

if not os.path.isfile(sys.argv[1]):
    print "Unable to find release notes at:", sys.argv[1]
    exit(1)

release_notes = os.path.realpath(sys.argv[1])

boost_site.site_tools.init()

# Note: Not using os.path.relpath because I want to support Python 2.5
cwd = os.getcwd()
if not release_notes.startswith(cwd):
    print "Release notes aren't in current site:", sys.argv[1]
    exit(1)

release_notes = release_notes[len(cwd):].lstrip('/')

# Update all the pages first so that the state file is up to date.
# TODO: Might be enough just to scan for new pages?
boost_site.site_tools.update_quickbook()

# Flag the released page
pages = boost_site.site_tools.load_pages()
if release_notes not in pages.pages:
    print "Unable to find page:", release_notes
    exit(1)
pages.pages[release_notes].flags.add('released')
pages.save()

# Update again to reflect the new state.
boost_site.site_tools.update_quickbook()
