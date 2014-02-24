<?php

require_once(__DIR__.'/../boost_libraries.php');

$libraries = boost_libraries::from_xml('<?xml version="1.0" encoding="US-ASCII"?>
<boost xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <categories>
    <category name="String">
      <title>String and text processing</title>
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

$accumulators_details = '<library>
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
  </library>';

$libraries->update($accumulators_details, '1.36.0');
$r = $libraries->get_history('accumulators');
assert(count($r) == 1);

$libraries->update($accumulators_details, 'develop');
$r = $libraries->get_history('accumulators');
assert(count($r) == 1);

$new_accumulators_details = '<library>
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
    <category>Generic</category>
  </library>';

$libraries->update($new_accumulators_details, 'develop');
$r = $libraries->get_history('accumulators');
assert(count($r) == 2);
assert(isset($r['1.36.0']));
assert(isset($r['develop']));
assert($r['1.36.0']['category'] == array('Math'));
assert($r['develop']['category'] == array('Math', 'Generic'));

$libraries->update($new_accumulators_details, 'master');
$r = $libraries->get_history('accumulators');
assert(count($r) == 2);
assert(isset($r['1.36.0']));
assert(isset($r['master']));
assert(!isset($r['develop']));
assert($r['1.36.0']['category'] == array('Math'));
assert($r['master']['category'] == array('Math', 'Generic'));