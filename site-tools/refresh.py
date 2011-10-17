#!/usr/bin/env python
# Copyright 2007 Rene Rivera
# Copyright 2011 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

"""Usage: python refresh.py

Reconvert all the quickbook files and regenerate the html pages. Does
not update the rss feeds or add new pages. Useful for when quickbook,
the scripts or the templates have been updated.
"""

import boost_site.site_tools

boost_site.site_tools.init()
boost_site.site_tools.update_quickbook()
