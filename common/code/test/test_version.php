<?php

require_once(__DIR__.'/../boost_version.php');

$develop = BoostVersion::develop();
$master = BoostVersion::master();
$boost_1_55_0 = BoostVersion::release(1, 55, 0);
$boost_1_54_0 = BoostVersion::release(1, 54, 0);

assert($develop->compare($master) > 0);
assert($master->compare($develop) < 0);
assert($develop->compare($boost_1_55_0) > 0);
assert($boost_1_55_0->compare($develop) < 0);
assert($boost_1_55_0->compare($boost_1_54_0) > 0);
assert($boost_1_54_0->compare($boost_1_55_0) < 0);

assert($boost_1_55_0->compare('boost_1_55_0') == 0);
assert($boost_1_55_0->compare('boost_1_54_0') > 0);
assert($boost_1_55_0->compare('boost_1_56_0') < 0);

assert($develop->dir() == 'develop');
assert($master->dir() == 'master');
assert($boost_1_55_0->dir() == 'boost_1_55_0');
assert((string) $boost_1_55_0 == '1.55.0');
