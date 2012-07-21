<?php

if (strpos($_SERVER['REQUEST_URI'], '//') !== FALSE)
{
	header("Location: http://$_SERVER[HTTP_HOST]".
		preg_replace('@//+@','/', $_SERVER['REQUEST_URI']),
		TRUE, 301);
	exit(0);
}

if (strncmp($_SERVER['REQUEST_URI'], '/doc/libs/1_', 12) == 0  &&
        is_dir("$_SERVER[DOCUMENT_ROOT]/$_SERVER[REQUEST_URI]") &&
        is_readable("$_SERVER[DOCUMENT_ROOT]/$_SERVER[REQUEST_URI]index.html"))
{
    readfile("$_SERVER[DOCUMENT_ROOT]/$_SERVER[REQUEST_URI]/index.html");
    exit(0);
}

require_once(dirname(__FILE__) . '/../common/code/boost_archive.php');

function boost_compare_version($version)
{
    if ($version && preg_match('@([0-9]+)_([0-9]+)_([0-9]+)@',$version,$vinfo))
    {
        array_shift($vinfo);

        global $boost_current_version;
        $v = $boost_current_version[0];
        $r = $boost_current_version[1];
        $p = $boost_current_version[2];

        return
          $v < $vinfo[0] ? 1 :
          ($v > $vinfo[0] ? -1 :
          ($r < $vinfo[1] ? 1 :
          ($r > $vinfo[1] ? -1 :
          ($p < $vinfo[2] ? 1 :
          ($p > $vinfo[2] ? -1
            : 0)))));
    }
    else
    {
        return FALSE;
    }
}

function add_spirit_analytics($content) {
    $server = $_SERVER['HTTP_HOST'];
    
    if ($server != 'www.boost.org' && $server != 'live.boost.org')
        return $content;

    if(stripos($content, '_uacct = "UA-11715441-2"') !== FALSE)
        return $content;

    $analytics = <<<EOS
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(
    ['_setAccount', 'UA-11715441-2'],
    ['_trackPageview'],
    ['_setDomainName', '$server'],
    ['_setAllowLinker', true]
    );

  (function() {
    var ga = document.createElement('script');
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    ga.setAttribute('async', 'true');
    document.documentElement.firstChild.appendChild(ga);
  })();
</script>
EOS;

    $content = preg_replace(
        '@<a\s+href="(http://spirit.sourceforge.net[^"]*)"@i',
        '<a href="${1}" onclick=\'_gaq.push(["_link", "${1}"]); return false;\'',
        $content );

    return str_ireplace('</head>', $analytics.'</head>', $content);
}

$location = get_archive_location('@^[/]([^/]+)[/](.*)$@',$_SERVER["PATH_INFO"],true,false);
$compare_version = boost_compare_version($location['version']);

display_from_archive(
  $location,
  array(
  //~ special cases that can't be processed at all (some redirects)
  array('@.*@','@^libs/gil/doc/.*(html|htm)$@i','raw','text/html'),
  array('@.*@','@^libs/preprocessor/doc/.*(html|htm)$@i','raw','text/html'),
  array('@.*@','@^libs/test/doc/components/test_tools/reference/.*(html|htm)$@i','raw','text/html'),
  array('@.*@','@^libs/test/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/spirit/.*(html|htm)$@i','simple','text/html', 'add_spirit_analytics'),
  array('@.*@','@^libs/fusion/.*(html|htm)$@i','basic','text/html', 'add_spirit_analytics'),
  array('@.*@','@^libs/wave/.*(html|htm)$@i','raw','text/html'),
  array('@.*@','@^libs/range/doc/.*(html|htm)$@i','raw','text/html'),
  //~ special cases that can't be embeded in the standard frame
  array('@.*@','@^libs/locale/doc/.*(html|htm)$@i','raw','text/html'),
  array('@.*@','@^libs/iostreams/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/serialization/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/filesystem/(v\d/)?doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/system/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/numeric/conversion/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/optional/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/polygon/doc/.*(html|htm)$@i','simple','text/html'),
  //~ default to processed output for libs and tools
  array('@.*@','@^libs/[^/]+/doc/html/.*(html|htm)$@i','basic','text/html'),
  array('@.*@','@^libs/[^/]+/doc/[^/]+/html/.*(html|htm)$@i','basic','text/html'),
  array('@.*@','@^libs/[^/]+/doc/[^/]+/doc/html/.*(html|htm)$@i','basic','text/html'),
  array('@.*@','@^libs.*(html|htm)$@i','basic','text/html'),
  array('@.*@','@^tools.*(html|htm)$@i','basic','text/html'),
  array('@.*@','@^doc/html/.*html$@i','boost_book_basic','text/html'),
  array('@.*@','@^more/.*html$@i','basic','text/html'),
  //~ the headers are text files displayed in an embeded page
  array('@.*@','@^boost/.*$@i','cpp','text/plain')
  ),
  null,
  $compare_version === -1 ? "+1 year" :
    ($compare_version === 0 ? "+1 week" : "+1 day")
);
