#!/bin/bash

BOOST_WEBSITE_SHARED_DIR=/opt/www/shared
mkdir -p $BOOST_WEBSITE_SHARED_DIR/archives/live
cd $BOOST_WEBSITE_SHARED_DIR/archives/live
wget https://archives.boost.io/release/1.85.0/source/boost_1_85_0.tar.gz
tar -xvf boost_1_85_0.tar.gz
