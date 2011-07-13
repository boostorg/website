Boost website generator
=======================

This site contains several scripts for maintaining the Boost website.
These are:

site-tools.py
-------------

A script to generate several of the pages on the Boost website,
including the release notes, the home page and the downlaod page,
as well as some other data for the site.

Usage: python site-tools.py [command]

Commands:

update      Update the html pages and rss feeds for new or updated
            quickbook files.

refresh     Reconvert all the quickbook files and regenerate the html
            pages. Does not update the rss feeds or add new pages.
            Useful for when quickbook, the scripts or the templates have
            been updated.

update-doc-list.php
-------------------

Updates the documentation list from `doc/libraries.xml`.
Requires php the site to be configured.

----------------------------------------------------------------------

Copyright 2011 Daniel James

Distributed under the Boost Software License, Version 1.0.
See accompanying file LICENSE_1_0.txt or
http://www.boost.org/LICENSE_1_0.txt 
