<?php

use Tester\Assert;

require_once(__DIR__.'/../config/bootstrap.php');
require_once(__DIR__.'/../../boost_documentation.php');

BoostFilter::$template = __DIR__.'/template.php';

$failure_count = 0;

function filter_test($filter, $data, $content, $expected) {
    ob_start();
    echo_filtered($filter, $data, $content);
    $result = ob_get_clean();
    Assert::same(trim($expected), trim($result));
}

/* Plain Text */

$test_text = <<<EOL
Hello World!
EOL;

$test_text_expected = <<<EOL
<!DOCTYPE html>

<html>
<head>
<meta name="viewport" content="width=device-width,initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Hello_world_test.txt</title></head>
<body>
<h3>Hello_world_test.txt</h3>
<pre>
Hello World!</pre>
</body>
</html>
EOL;

$data = new BoostDocumentation();
$data->path = 'Hello_world_test.txt';
filter_test('text', $data, $test_text, $test_text_expected);

/* UTF-8 Plain Text
 */

$test_text = <<<EOL
Iñtërnâtiônàlizætiøn
EOL;

$test_text_expected = <<<EOL
<!DOCTYPE html>

<html>
<head>
<meta name="viewport" content="width=device-width,initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Hello_world_test.txt</title></head>
<body>
<h3>Hello_world_test.txt</h3>
<pre>
I&ntilde;t&euml;rn&acirc;ti&ocirc;n&agrave;liz&aelig;ti&oslash;n</pre>
</body>
</html>
EOL;

$data = new BoostDocumentation();
$data->path = 'Hello_world_test.txt';
filter_test('text', $data, $test_text, $test_text_expected);


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

if (version_compare(PHP_VERSION, '5.4.0') < 0) {
    $test_text_expected = <<<EOL
<!DOCTYPE html>

<html>
<head>
<meta name="viewport" content="width=device-width,initial-scale=1.0" />
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
} else {
    $test_text_expected = <<<EOL
<!DOCTYPE html>

<html>
<head>
<meta name="viewport" content="width=device-width,initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Hello_world_test.txt</title></head>
<body>
<h3>Hello_world_test.txt</h3>
<pre>
{$unknown_character}
I&ntilde;t&euml;rn&acirc;ti&ocirc;n&agrave;liz&aelig;ti&oslash;n</pre>
</body>
</html>
EOL;
}

$data = new BoostDocumentation();
$data->path = 'Hello_world_test.txt';
filter_test('text', $data, $test_text, $test_text_expected);

/* C++ */

$test_cpp = <<<EOL
#include <boost/config.hpp>

int main() {}
EOL;

$test_cpp_expected = <<<EOL
<!DOCTYPE html>

<html>
<head>
<meta name="viewport" content="width=device-width,initial-scale=1.0" />
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

$data = new BoostDocumentation();
$data->path = 'foo/test.cpp';
filter_test('cpp', $data, $test_cpp, $test_cpp_expected);

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

$data = new BoostDocumentation();
filter_test('simple', $data, $test_doc, $test_simple_expected);
// TODO: This doesn't work because the filter calls 'virtual', which breaks
// out of the buffered output.
//filter_test('basic', $params, '');
