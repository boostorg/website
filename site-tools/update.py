#!/usr/bin/env python
# Copyright 2007 Rene Rivera
# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

"""Usage: python update.py

Update the html pages and rss feeds for new or updated quickbook files.

"""

import boost_site.site_tools

boost_site.site_tools.init()
boost_site.site_tools.update_quickbook()
