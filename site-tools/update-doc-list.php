<?php

echo "Serializing library info\n";

require_once(dirname(__FILE__) . '/../common/code/boost_libraries.php');

$libs = new boost_libraries(dirname(__FILE__) . '/../doc/libraries.xml');
file_put_contents(dirname(__FILE__) . '/../doc/libraries.txt', serialize($libs));
