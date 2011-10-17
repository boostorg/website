# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.  (See accompanying
# file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

import re
import boost_site.site_tools

#
# Upgrades
#

def upgrade1():
    pages = boost_site.site_tools.load_pages()
    for qbk_file in pages.pages:
        page = pages.pages[qbk_file]
        if re.match('users/(download|history)', page.location) and \
                page.pub_date != 'In Progress':
            page.flags.add('released')
    pages.save()

versions = [
        upgrade1
        ]

#
# Implementation
#

def upgrade():
    version = Version()

    if(version.version < len(versions)):
        print "Upgrading to new version."

        for v in range(version.version, len(versions)):
            print "Upgrade", v + 1
            versions[v]()
            version.version = v + 1
            version.save()

class Version:
    def __init__(self):
        self.filename = 'site-tools/state/version.txt'
        self.load()

    def load(self):
        version_file = open(self.filename, "r")
        try:
            self.version = int(version_file.read())
        finally:
            version_file.close()

    def save(self):
        version_file = open(self.filename, "w")
        try:
            version_file.write(str(self.version))
        finally:
            version_file.close()
