<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Filter Tests</title>
</head>
<body>
<h1>Filter Tests</h1>

<?php

require_once(dirname(__FILE__) . '/../boost_documentation.php');

BoostFilters::$template = __DIR__.'/template.php';

$failure_count = 0;

function filter_test($filter, $params, $expected) {
    ob_start();
    echo_filtered($filter, $params);
    $result = ob_get_clean();
    
    if(trim($result) != trim($expected)) {
        global $failure_count;
        ++$failure_count;
        
        echo
            "<h2>Failure for filter {$filter}</h2>",
            '<p>Expected:</p><pre>',
            html_encode($expected),
            '</pre><p>Result:</p><pre>',
            html_encode($result),
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
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Hello_world_test.txt</title></head>
<body>
<h3>Hello_world_test.txt</h3>
<pre>
Hello World!</pre>
</body>
</html>
EOL;

$params = Array(
    'key' => 'Hello_world_test.txt',
    'content' => $test_text
);

filter_test('text', $params, $test_text_expected);

/* UTF-8 Plain Text
 */

$test_text = <<<EOL
Iñtërnâtiônàlizætiøn
EOL;

$test_text_expected = <<<EOL
<!DOCTYPE html>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Hello_world_test.txt</title></head>
<body>
<h3>Hello_world_test.txt</h3>
<pre>
I&ntilde;t&euml;rn&acirc;ti&ocirc;n&agrave;liz&aelig;ti&oslash;n</pre>
</body>
</html>
EOL;

$params = Array(
    'key' => 'Hello_world_test.txt',
    'content' => $test_text
);

filter_test('text', $params, $test_text_expected);


/* Non-UTF-8 Plain Text
 *
 * We can't practically suppport encodings other than UTF-8, so if
 * the text isn't valid UTF-8 then use a fallback, the unknown character
 * symbol.
 */

$unknown_character = "\xef\xbf\xbd";

// The second line is valid UTF-8, but because the string was
// rejected it still gets marked as an unknown character.
$test_text = <<<EOL
\xb6
Iñtërnâtiônàlizætiøn
EOL;

$test_text_expected = <<<EOL
<!DOCTYPE html>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Hello_world_test.txt</title></head>
<body>
<h3>Hello_world_test.txt</h3>
<pre>
{$unknown_character}
I{$unknown_character}{$unknown_character}t{$unknown_character}{$unknown_character}rn{$unknown_character}{$unknown_character}ti{$unknown_character}{$unknown_character}n{$unknown_character}{$unknown_character}liz{$unknown_character}{$unknown_character}ti{$unknown_character}{$unknown_character}n</pre>
</body>
</html>
EOL;

$params = Array(
    'key' => 'Hello_world_test.txt',
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
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>foo/test.cpp</title></head>
<body>
<h3>foo/test.cpp</h3>
<pre>
#include &lt;<a href="../boost/config.hpp">boost/config.hpp</a>&gt;

int main() {}</pre>
</body>
</html>
EOL;

$params = Array(
    'key' => 'foo/test.cpp',
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
    'content' => $test_doc
);

$test_simple_expected = <<<EOL
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

filter_test('simple', $params, $test_simple_expected);
// TODO: This doesn't work because the filter calls 'virtual', which breaks
// out of the buffered output.
//filter_test('basic', $params, '');

echo $failure_count > 0 ? "<p>Failure count: $failure_count</p>" : "<p>All passed</p>";

?>
</body>
</html>
