Boost website generator
=======================

A script to generate several of the pages on the Boost website,
including the release notes, the home page and the downlaod page,
as well as some other data for the site.

Usage: python site-gen.py [command]

Commands:

update      Update the html pages and rss feeds for new or updated
            quickbook files.

refresh     Reconvert all the quickbook files and regenerate the html
            pages. Does not update the rss feeds or add new pages.
            Useful for when quickbook, the scripts or the templates have
            been updated.

docs        Update the documentation list from doc/libraries.xml.
            Requires php to be on the path and the site to be configured.

start       Setup the state file and regenerate html files from the old
            hashes files.

----------------------------------------------------------------------

Copyright 2011 Daniel James

Distributed under the Boost Software License, Version 1.0.
See accompanying file LICENSE_1_0.txt or
http://www.boost.org/LICENSE_1_0.txt 
