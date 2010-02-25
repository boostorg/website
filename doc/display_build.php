<?php
require_once(dirname(__FILE__) . '/../common/code/boost_archive.php');

function add_boost_build_analytics($content) {
    $analytics = <<<EOS
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-2917240-2']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script');
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    ga.setAttribute('async', 'true');
    document.documentElement.firstChild.appendChild(ga);
  })();
</script>
EOS;

    return stripos($content, '_uacct = "UA-2917240-2"') !== FALSE ? $content :
        str_ireplace('</head>', $analytics.'</head>', $content);
}

display_from_archive(
  get_archive_location(
    '@^[/]([^/]+)[/](.*)$@',
    $_SERVER["PATH_INFO"],
    ,false,false
  ),
  array(
  //~ array(version-regex,path-regex,raw|simple|text|cpp|boost_book_html|boost_libs_html,mime-type[,preprocess hook]),
  array('@.*@','@^boost-build/index[.]html$@i','simple','text/html', 'add_boost_build_analytics'),
  array('@.*@','@[.](html|htm)$@i','boost_book_html','text/html')
));
