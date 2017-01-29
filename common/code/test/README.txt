Most of the website is completely self-contained, but the tests use composer
to pull in some dependencies. You'll need to install composer from
https://getcomposer.org/

Then in this directory run:

    composer install

And that should install all the dependencies into the `vendor` directory.

You should now be able to run individual tests directly:

    php filters.phpt

Or run all the tests using something like:

    ./vendor/bin/tester -p php -c config/php.ini tests

Although for a full test including things like http headers, should try to use
a `php-cgi` executable.
