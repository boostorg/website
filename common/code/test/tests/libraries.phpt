<?php

use Tester\Assert;

require_once(__DIR__.'/../config/bootstrap.php');
require_once(__DIR__.'/../../boost.php');

$libraries = BoostLibraries::from_xml('<?xml version="1.0" encoding="UTF-8"?>
<boost xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <categories>
    <category name="Math">
      <title>Maths stuff.</title>
    </category>
    <category name="Generic">
      <title>Nonbranded stuff.</title>
    </category>
  </categories>
  <library>
    <key>accumulators</key>
    <library_path>libs/accumulators</library_path>
    <boost-version>1.36.0</boost-version>
    <name>Accumulators</name>
    <authors>Eric Niebler</authors>
    <description>Framework for incremental calculation, and collection of statistical accumulators.</description>
    <documentation>libs/accumulators/</documentation>
    <category>Math</category>
  </library>
</boost>
');

$accumulators_details = '{
    "key" : "accumulators",
    "library_path": "libs/accumulators",
    "name": "Accumulators",
    "authors": "Eric Niebler",
    "description": "Framework for incremental calculation, and collection of statistical accumulators.",
    "documentation": "libs/accumulators/",
    "category": [ "math" ]
}';

$libraries->update('1.36.0', BoostLibrary::read_libraries_json($accumulators_details));
$r = $libraries->get_history('accumulators');
Assert::same(count($r), 1);
Assert::same((string) $libraries->latest_version, '1.36.0');

$libraries->update('develop', BoostLibrary::read_libraries_json($accumulators_details));
$r = $libraries->get_history('accumulators');
Assert::same(count($r), 1);

$new_accumulators_details = '{
    "key": "accumulators",
    "library_path": "libs/accumulators",
    "boost-version": "1.36.0",
    "name": "Accumulators",
    "authors": "Eric Niebler",
    "description": "Framework for incremental calculation, and collection of statistical accumulators.",
    "documentation": "libs/accumulators/",
    "category": [ "Math", "Generic" ]
}';

$libraries->update('develop', BoostLibrary::read_libraries_json($new_accumulators_details));
$r = $libraries->get_history('accumulators');
Assert::same(count($r), 2);
Assert::true(isset($r['1.36.0']));
Assert::true(isset($r['develop']));
Assert::same($r['1.36.0']->details['category'], array('math'));
Assert::same($r['develop']->details['category'], array('generic', 'math'));
Assert::false(isset($r['master']));

$libraries->update('master', BoostLibrary::read_libraries_json($new_accumulators_details));
$r = $libraries->get_history('accumulators');
Assert::same(count($r), 2);
Assert::true(isset($r['1.36.0']));
Assert::true(isset($r['master']));
Assert::false(isset($r['develop']));
Assert::same($r['1.36.0']->details['category'], array('math'));
Assert::same($r['master']->details['category'], array('generic', 'math'));
Assert::false(isset($r['develop']));

$libraries2 = BoostLibraries::from_xml($libraries->to_xml());
$r = $libraries->get_history('accumulators');
Assert::same(count($r), 2);
Assert::true(isset($r['1.36.0']));
Assert::true(isset($r['master']));
Assert::false(isset($r['develop']));
Assert::same($r['1.36.0']->details['category'], array('math'));
Assert::same($r['master']->details['category'], array('generic', 'math'));
Assert::false(isset($r['develop']));
Assert::same((string) $libraries->latest_version, '1.36.0');
