Boost website site tools
========================

This directory contains several scripts for maintaining the Boost website.
Some scripts with '--help' for more info.

Updating the website
--------------------

update-pages.php:

    Update the html pages and rss feeds for new or updated quickbook files.

refresh-pages.php:

    Reconvert all the quickbook files and regenerate the html pages. Does not
    update the rss feeds or add new pages. Useful for when quickbook, the
    scripts or the templates have been updated.

update-doc-list.php:

    Updates the documentation list from `doc/libraries.xml`, or from a boost
    release/repo. Run from a cron job for the git development branches, but
    will need to be run manually for an actual release.

Maintaining a release
---------------------

After running these scripts, will need to run update-pages.php.

scan-documentation.php:

    After adding documentation to the server, run this script which
    should detect it and add the documentation to release data.

load-release-data.php:

    After uploading a release to sourceforge, add it to the release
    data using this script.

set-release-status.php:

    Use this script to mark a version as released (including beta versions).

Misc
----

update-repo.php:

    Updates the boost super project from the metadata stored for the
    website. Almost always run from a cron job.

new-libraries.php:

    New library text for the release notes.
