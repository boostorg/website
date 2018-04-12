<?php

use Tester\Assert;

require_once(__DIR__.'/../config/bootstrap.php');
require_once(__DIR__.'/../../boost.php');

# Some of these tests have other correct values.
# e.g. might quote attributes values differently.

# Relative link
Assert::same(
    '<a href="https://www.boost.org/a/b/c.txt">',
    BoostSiteTools::base_links(
        '<a href="../b/c.txt">',
        'https://www.boost.org/a/d/e.html'));

# HTTP base
Assert::same(
    '<a href="http://www.boost.org/a/b/c.txt">',
    BoostSiteTools::base_links(
        '<a href="../b/c.txt">',
        'http://www.boost.org/a/d/e.html'));

# Absolute link
Assert::same(
    '<A hReF="http://svn.boost.org/trac/wiki">',
    BoostSiteTools::base_links(
        '<A hReF="http://svn.boost.org/trac/wiki">',
        'https://www.boost.org/a/d/e.html'));

# Root link
Assert::same(
    '<img class=something src="https://www.boost.org/logo.png">',
    BoostSiteTools::base_links(
        '<img class=something src="/logo.png">',
        'https://www.boost.org/a/d/e.html'));

# Unquoted
Assert::same(
    '<img src="https://www.boost.org/logo.png">',
    BoostSiteTools::base_links(
        '<img src=/logo.png>',
        'https://www.boost.org/a/d/e.html'));

# Encoding quotes
Assert::same(
    '<img src="https://www.boost.org/&quot;logo.png&quot;">',
    BoostSiteTools::base_links(
        '<img src=\'/"logo.png"\'>',
        'https://www.boost.org/a/d/e.html'));

# Don't match quoted text in tags, but do out of tags.
Assert::same(
    '<p blah="<img src=/logo.png>">"<img src="https://www.boost.org/logo.png">"',
    BoostSiteTools::base_links(
        '<p blah="<img src=/logo.png>">"<img src=/logo.png>"',
        'https://www.boost.org/a/d/e.html'));
