<?php

use Tester\Assert;

require_once(__DIR__.'/../config/bootstrap.php');
require_once(__DIR__.'/../../boost.php');

Assert::same('&lt;test&gt;', html_encode('<test>'));
Assert::same('��', html_encode(urldecode('%A0%A0')));
