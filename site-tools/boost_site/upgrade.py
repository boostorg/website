# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.  (See accompanying
# file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

import re
import boost_site.site_tools

#
# Upgrades
#

def upgrade_old():
    print("Old, unsupported data version.")
    return False

versions = [upgrade_old] * 4
versions.extend([])

#
# Implementation
#

def upgrade():
    version = Version()

    if(version.version < len(versions)):
        print("Upgrading to new version.")

        for v in range(version.version, len(versions)):
            print("Upgrade " + str(v + 1))
            if not versions[v]():
                raise Exception("Error upgrading to version v")
            version.version = v + 1
            version.save()

class Version:
    def __init__(self):
        self.filename = 'generated/state/version.txt'
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
