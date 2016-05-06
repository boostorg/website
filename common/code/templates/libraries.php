<?php
require_once(__DIR__.'/../boost.php');
if (!isset($version)) { $version = BoostVersion::current(); }
if (!isset($libs)) { $libs = BoostLibraries::load(); }
$categorized = $libs->get_categorized_for_version($version);
$alphabetic = $libs->get_for_version($version);
uasort($categorized, function($a, $b) {
    $a = $a['title'];
    $b = $b['title'];
    if ($a === 'Miscellaneous') { $a = 'ZZZZZZZZ'; }
    if ($b === 'Miscellaneous') { $b = 'ZZZZZZZZ'; }
    return ($a > $b) ?: ($a < $b ? -1 : 0);
});

function rewrite_link($link) {
    if(preg_match('@^/?libs/(.*)@', $link, $matches)) {
        $link = $matches[1];
    }
    else {
        $link = '../'.ltrim($link, '/');
    }
    return preg_replace('@/$@', '/index.html', $link);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>
<meta http-equiv="Content-Type"
content="text/html; charset=iso-8859-1">
<meta name="ProgId" content="FrontPage.Editor.Document">
<meta name="GENERATOR" content="Microsoft FrontPage 5.0">
<title>Boost Libraries</title>
<link rel="stylesheet" href="../doc/src/boostbook.css" type="text/css" />
</head>

<body bgcolor="#FFFFFF" text="#000000">

  <table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111">
    <tr>
      <td width="277">
        <a href="../index.html">
        <img src="../boost.png" alt="boost.png (6897 bytes)" align="middle" width="277" height="86" border="0"></a></td>
      <td width="337" align="middle">
        <font size="7">Libraries</font>
      </td>
    </tr>
  </table>

  <table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" bgcolor="#D7EEFF" height="26" width="673">
    <tr>
      <td height="16" width="663"><a href="../more/getting_started/index.html">Getting Started</a>&nbsp;&nbsp;<font color="#FFFFFF">&nbsp;
      </font>&nbsp;&nbsp;&nbsp;&nbsp; <a href="../tools/index.html">Tools&nbsp;</a>&nbsp;<font color="#FFFFFF">&nbsp;
      </font>&nbsp;&nbsp;&nbsp;&nbsp; <a href="http://www.boost.org">Web Site</a>&nbsp;&nbsp;<font color="#FFFFFF">&nbsp;
      </font>&nbsp;&nbsp;&nbsp;&nbsp; <a href="http://www.boost.org/users/news/">News</a>&nbsp;&nbsp;<font color="#FFFFFF">&nbsp;
      </font>&nbsp;&nbsp;&nbsp;&nbsp; <a href="http://www.boost.org/community/">Community</a>&nbsp;&nbsp;<font color="#FFFFFF">&nbsp;
      </font>&nbsp;&nbsp;&nbsp;&nbsp;
      <a href="http://www.boost.org/users/faq.html">FAQ</a>&nbsp;&nbsp;<font color="#FFFFFF">&nbsp;
      </font>&nbsp;&nbsp;&nbsp;&nbsp; <a href="../more/index.htm">More Info</a></td>
    </tr>
  </table>

<dl>
  <dt><a href="#Alphabetically">Libraries Listed Alphabetically</a></dt>
  <dt><a href="#Category">Libraries Listed by Category</a></dt>
    <dl>
<?php
        foreach($categorized as $category) {
              echo "      <dt><a href=\"#{$category['name']}\">{$category['title']}</a></dt>\n";
        }
?>
    </dl>
  <dt><a href="#Removed">Libraries Retired from Boost</a></dt>
</dl>

<p>See <a href="../more/getting_started.html">Getting Started</a>  page to find out
how to download, build, and install the libraries.</p>

<hr>

<h2>Libraries Listed <a name="Alphabetically">Alphabetically</a></h2>

<ul>
<?php
    foreach($alphabetic as $lib) {
        echo "    <li><a href=\"".rewrite_link($lib['documentation'])."\">{$lib['name']}</a> - ";
        echo rtrim(trim($lib['description']), '.');
        if (!empty($lib['authors'])) {
            #echo ", from ", implode(',', $lib['authors']);
            echo ", from {$lib['authors']}";
        }
        echo ".</li>\n";
    }
?>
</ul>

<hr>

<h2>Libraries Listed by <a name="Category">Category</a></h2>

<?php

    foreach($categorized as $category) {
        echo "<h3><a name=\"{$category['name']}\">{$category['title']}</a></h3>\n\n";
        echo "<ul>\n";
        foreach ($category['libraries'] as $lib) {
            echo "    <li><a href=\"".rewrite_link($lib['documentation'])."\">{$lib['name']}</a> - ";
            echo rtrim(trim($lib['description']), '.');
            if (!empty($lib['authors'])) {
                #echo ", from ", implode(',', $lib['authors']);
                echo ", from {$lib['authors']}\n";
            }
        }
        echo "</ul>\n\n";
    }
?>

<p>[Category suggestions from Aleksey Gurtovoy, Beman Dawes and Vicente J. Botet Escrib&aacute;]</p>

<hr>

<h2>Libraries <a name="Removed">Retired</a> from Boost</h2>

<ul>
    <li>compose - Functional composition adapters for the STL,
        from Nicolai Josuttis.  Removed in Boost version 1.32.
        Please use <a href="bind/bind.html">Bind</a> or <a
        href="lambda/index.html">Lambda</a> instead.</li>
</ul>

<hr>

<p>Revised
<!--webbot bot="Timestamp" s-type="EDITED"
s-format="%d %b %Y" startspan -->19 Feb 2015<!--webbot bot="Timestamp" endspan i-checksum="14409" --></p>

<p>&copy; Copyright Beman Dawes 2000-2004</p>
<p>Distributed under the Boost Software License, Version 1.0.
(See file <a href="../LICENSE_1_0.txt">LICENSE_1_0.txt</a>
or <a href="http://www.boost.org/LICENSE_1_0.txt">www.boost.org/LICENSE_1_0.txt</a>)
</p>

</body>
</html>
