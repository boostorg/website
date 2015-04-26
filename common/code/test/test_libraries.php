<?php

require_once(__DIR__.'/../boost.php');

$libraries = BoostLibraries::from_xml('<?xml version="1.0" encoding="US-ASCII"?>
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
    <module>accumulators</module>
    <boost-version>1.36.0</boost-version>
    <name>Accumulators</name>
    <authors>Eric Niebler</authors>
    <description>Framework for incremental calculation, and collection of statistical accumulators.</description>
    <documentation>libs/accumulators/</documentation>
    <std-proposal>false</std-proposal>
    <std-tr1>false</std-tr1>
    <category>Math</category>
  </library>
</boost>
');

$accumulators_details = '{
    "key" : "accumulators",
    "module": "accumulators",
    "boost-version": "1.36.0",
    "name": "Accumulators",
    "authors": "Eric Niebler",
    "description": "Framework for incremental calculation, and collection of statistical accumulators.",
    "documentation": "libs/accumulators/",
    "category": [ "Math" ]
}';

$libraries->update(BoostLibrary::read_libraries_json($accumulators_details), '1.36.0');
$r = $libraries->get_history('accumulators');
assert(count($r) == 1);

$libraries->update(BoostLibrary::read_libraries_json($accumulators_details), 'develop');
$r = $libraries->get_history('accumulators');
assert(count($r) == 1);

$new_accumulators_details = '{
    "key": "accumulators",
    "module": "accumulators",
    "boost-version": "1.36.0",
    "name": "Accumulators",
    "authors": "Eric Niebler",
    "description": "Framework for incremental calculation, and collection of statistical accumulators.",
    "documentation": "libs/accumulators/",
    "category": [ "Math", "Generic" ]
}';

$libraries->update(BoostLibrary::read_libraries_json($new_accumulators_details), 'develop');
$r = $libraries->get_history('accumulators');
assert(count($r) == 2);
assert(isset($r['1.36.0']));
assert(isset($r['develop']));
assert($r['1.36.0']->details['category'] == array('Math'));
assert($r['develop']->details['category'] == array('Generic', 'Math'));
assert(!isset($r['master']));

$libraries->update(BoostLibrary::read_libraries_json($new_accumulators_details), 'master');
$r = $libraries->get_history('accumulators');
assert(count($r) == 2);
assert(isset($r['1.36.0']));
assert(isset($r['master']));
assert(!isset($r['develop']));
assert($r['1.36.0']->details['category'] == array('Math'));
assert($r['master']->details['category'] == array('Generic', 'Math'));
assert(!isset($r['develop']));
