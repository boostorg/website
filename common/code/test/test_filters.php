<!DOCTYPE html>
<html>
<head>
<title>Filter Tests</title>
</head>
<body>
<h1>Filter Tests</h1>

<?php

require_once(dirname(__FILE__) . '/../boost_archive.php');
$template = dirname(__FILE__) . '/test_template.php';

$failure_count = 0;

function filter_test($filter, $params, $expected) {
    ob_start();
    echo_filtered($filter, $params);
    $result = ob_get_clean();
    
    if(trim($result) != trim($expected)) {
        global $failure_count;
        ++$failure_count;
        
        echo
            '<h2>Failure for filter $filter</h2>',
            '<p>Expected:</p><pre>',
            htmlentities($expected),
            '</pre><p>Result:</p><pre>',
            htmlentities($result),
            '</pre>';
    }
}

/* Plain Text */

$test_text = <<<EOL
Hello World!
EOL;

$test_text_expected = <<<EOL
<!DOCTYPE html>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=US-ASCII" />
<title>Boost C++ Libraries - Hello_world_test.txt</title></head>
<body>
<h3>Hello_world_test.txt</h3>
<pre>
Hello World!</pre>
</body>
</html>
EOL;

$params = Array(
    'template' => $template,
    'key' => 'Hello_world_test.txt',
    'charset' => 'US-ASCII',
    'content' => $test_text
);

filter_test('text', $params, $test_text_expected);

/* C++ */

$test_cpp = <<<EOL
#include <boost/config.hpp>

int main() {}
EOL;

$test_cpp_expected = <<<EOL
<!DOCTYPE html>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=US-ASCII" />
<title>Boost C++ Libraries - foo/test.cpp</title></head>
<body>
<h3>foo/test.cpp</h3>
<pre>
#include &lt;<a href="../boost/config.hpp">boost/config.hpp</a>&gt;

int main() {}</pre>
</body>
</html>
EOL;

$params = Array(
    'template' => $template,
    'key' => 'foo/test.cpp',
    'charset' => 'US-ASCII',
    'content' => $test_cpp
);

filter_test('cpp', $params, $test_cpp_expected);

/* HTML */

$test_doc = <<<EOL
<!DOCTYPE html>
<html>
<head>
<title>Test Document</title>
</head>
<body>
<h1>Simple test case for the filters</h1>
<p>For now just test a
<a href="http://svn.boost.org/trac/boost/">link to an external site</a>,
<a href="/tools/boostbook/">an absolute path</a> and
<a href="../../../development/">a relative path</a>.
</p>
</body>
</html>
EOL;

$params = Array(
    'template' => $template,
    'content' => $test_doc
);

filter_test('boost_book_html', $params, '');
filter_test('boost_libs', $params, '');
filter_test('simple', $params, '');
filter_test('basic', $params, '');

/* Frames */

//boost_frame1


echo $failure_count > 0 ? "<p>Failure count: $failure_count</p>" : "<p>All passed</p>";

?>
</body>
</html>