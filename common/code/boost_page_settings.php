<?php

# Copyright 2011, 2015 Daniel James
# Distributed under the Boost Software License, Version 1.0.
# (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)

class BoostPageSettings
{
    static $downloads = array(
        array(
            'anchor' => 'live',
            'single' => 'Current Release',
            'plural' => 'Current Releases',
            'matches' => array('feed/history/*.qbk|released'),
            'count' => 1
        ),
        array(
            'anchor' => 'beta',
            'single' => 'Beta Release',
            'plural' => 'Beta Releases',
            'matches' => array('feed/history/*.qbk|beta')
        )
    );

    # See boost_site.pages for matches pattern syntax.
    #
    # glob array( '|' flag )
    static $feeds = array(
        'generated/downloads.rss' => array(
            'link' => 'users/download/',
            'title' => 'Boost Downloads',
            'matches' => array('feed/history/*.qbk|released', 'feed/downloads/*.qbk'),
            'count' => 3
        ),
        'generated/history.rss' => array(
            'link' => 'users/history/',
            'title' => 'Boost History',
            'matches' => array('feed/history/*.qbk|released')
        ),
        'generated/news.rss' => array(
            'link' => 'users/news/',
            'title' => 'Boost News',
            'matches' => array('feed/news/*.qbk', 'feed/history/*.qbk|released'),
            'count' => 5
        ),
        'generated/dev.rss' => array(
            'link' => '',
            'title' => 'Release notes for work in progress boost',
            'matches' => array('feed/history/*.qbk'),
            'count' => 5
        )
    );
}
