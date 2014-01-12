<?php

echo "Serializing library info\n";

require_once(dirname(__FILE__) . '/../common/code/boost_libraries.php');

$libs = boost_libraries::from_file(dirname(__FILE__) . '/../doc/libraries.xml');
file_put_contents(dirname(__FILE__) . '/../generated/libraries.txt', serialize($libs));
file_put_contents(dirname(__FILE__) . '/../doc/libraries.xml.new', $libs->to_xml());
