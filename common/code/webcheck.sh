#!/bin/sh

/usr/bin/python24 /home/grafik/www.boost.org/testing/webcheck.py \
 --yank=http://beta.boost.org/doc/libs/ \
 --base-only \
 --quiet \
 --output=/home/grafik/www.boost.org/testing/webcheck \
 --force
