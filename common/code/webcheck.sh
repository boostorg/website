#!/bin/sh

/usr/bin/python24 /home/grafik/www.boost.org/testing/webcheck.py \
 "--yank=http://beta.boost.org/doc/libs" \
 "--yank=http://beta.boost.org/development/tests" \
 "--base-only" \
 "--quiet" \
 "--output=/home/grafik/www.boost.org/testing/webcheck" \
 "--force" \
 "http://beta.boost.org/"
