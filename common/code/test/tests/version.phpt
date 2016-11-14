<?php

use Tester\Assert;

require_once(__DIR__.'/../config/bootstrap.php');
require_once(__DIR__.'/../../boost.php');

$develop = BoostVersion::develop();
$master = BoostVersion::master();
$boost_1_55_0 = BoostVersion::release(1, 55, 0);
$boost_1_54_0 = BoostVersion::release(1, 54, 0);
$boost_1_56_0 = BoostVersion::release(1, 56, 0);
$boost_1_56_0_b1 = BoostVersion::release(1, 56, 0, 1);
$boost_1_56_0_b2 = BoostVersion::release(1, 56, 0, 2);
$boost_1_56_0_pre = BoostVersion::prerelease(1, 56, 0);

Assert::true($develop->compare($master) > 0);
Assert::true($master->compare($develop) < 0);
Assert::true($develop->compare($boost_1_55_0) > 0);
Assert::true($boost_1_55_0->compare($develop) < 0);
Assert::true($boost_1_55_0->compare($boost_1_54_0) > 0);
Assert::true($boost_1_54_0->compare($boost_1_55_0) < 0);

Assert::same($boost_1_55_0->compare('1_55_0'), 0);
Assert::true($boost_1_55_0->compare('1_54_0') > 0);
Assert::true($boost_1_55_0->compare('1_56_0') < 0);

Assert::same($develop->dir(), 'develop');
Assert::same($master->dir(), 'master');
Assert::same($boost_1_55_0->dir(), 'boost_1_55_0');
Assert::same((string) $boost_1_55_0, '1.55.0');

Assert::same($boost_1_56_0_pre->compare($boost_1_56_0_pre), 0);
Assert::true($boost_1_56_0_pre->compare($boost_1_56_0_b1) < 0);
Assert::true($boost_1_56_0_pre->compare($boost_1_56_0_b2) < 0);
Assert::true($boost_1_56_0_pre->compare($boost_1_56_0) < 0);

Assert::true($boost_1_56_0_b1->compare($boost_1_56_0_pre) > 0);
Assert::same($boost_1_56_0_b1->compare($boost_1_56_0_b1), 0);
Assert::true($boost_1_56_0_b1->compare($boost_1_56_0_b2) < 0);
Assert::true($boost_1_56_0_b1->compare($boost_1_56_0) < 0);

Assert::true($boost_1_56_0_b2->compare($boost_1_56_0_pre) > 0);
Assert::true($boost_1_56_0_b2->compare($boost_1_56_0_b1) > 0);
Assert::same($boost_1_56_0_b2->compare($boost_1_56_0_b2), 0);
Assert::true($boost_1_56_0_b2->compare($boost_1_56_0) < 0);

Assert::true($boost_1_56_0->compare($boost_1_56_0_pre) > 0);
Assert::true($boost_1_56_0->compare($boost_1_56_0_b1) > 0);
Assert::true($boost_1_56_0->compare($boost_1_56_0_b2) > 0);
Assert::same($boost_1_56_0->compare($boost_1_56_0), 0);

Assert::same($boost_1_56_0_pre->compare('1_56_0 prerelease'), 0);
Assert::same($boost_1_56_0_pre->compare('1.56.0prerelease'), 0);

Assert::same($boost_1_56_0_b1->compare('1_56_0beta'), 0);
Assert::same($boost_1_56_0_b1->compare('1_56_0b1'), 0);
Assert::same($boost_1_56_0_b1->compare('1_56_0_b1'), 0);
Assert::same($boost_1_56_0_b1->compare('1_56_0_beta1'), 0);
Assert::same($boost_1_56_0_b1->compare('1_56_0_beta'), 0);

Assert::same($boost_1_56_0_b2->compare('1_56_0b2'), 0);
Assert::same($boost_1_56_0_b2->compare('1_56_0_b2'), 0);
Assert::same($boost_1_56_0_b2->compare('1_56_0_beta2'), 0);
Assert::same($boost_1_56_0_b2->compare('1_56_0_beta_2'), 0);
Assert::same($boost_1_56_0_b2->compare('1.56.0 beta 2'), 0);

Assert::same($boost_1_55_0->git_ref(), 'boost-1.55.0');
Assert::same($boost_1_56_0_b1->git_ref(), 'boost-1.56.0-beta1');
