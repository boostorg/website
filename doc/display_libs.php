<?php
require_once(dirname(__FILE__) . '/../common/code/boost_archive.php');

function add_spirit_analytics($content) {
    if(stripos($content, '_uacct = "UA-11715441-2"') !== FALSE)
        return $content;

    $analytics = <<<EOS
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(
    ['_setAccount', 'UA-11715441-2'],
    ['_trackPageview'],
    ['_setDomainName', 'none'],
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

$_file = new boost_archive('@^[/]([^/]+)[/](.*)$@',$_SERVER["PATH_INFO"],array(
  //~ array(version-regex,path-regex,raw|simple|text|cpp|boost_book_html|boost_libs_html,mime-type[,preprocess hook]),
  //~ this handles most of the simple cases of index.htm(l) redirect files
  array(
    '@1_('.implode('|',array(
      '34','35',
      )).')_[0-9]@',
    '@^libs/('.implode('|',array(
      'accumulators','algorithm/string','any','array','asio','assign','bind','bimap',
      'circular_buffer',
      'concept_check','config','date_time','date_time/doc','disjoint_sets',
      'dynamic_bitset','exception','filesystem','foreach','function','functional/hash',
      'function_types','fusion','graph','interprocess','intrusive',
      'io','iostreams','iterator','lambda',
      'lambda/doc','math','math/doc','mem_fn','mpl',
      'multi_array','multi_index','numeric','numeric/conversion','numeric/interval/doc',
      'numeric/ublas','unmeric/ublas/doc','optional','parameter','pool','preprocessor',
      'program_options','program_options/doc','property_map','proto','ptr_container','python',
      'python/doc/tutorial','python/doc/v2','regex','serialization','signals',
      'signals/doc','smart_ptr','statechart','static_assert','system','test',
      'thread','thread/doc','tr1','tuple','typeof',
      'type_traits','units','unordered','variant','xpressive'
      )).')/index.(html|htm)$@i',
    'raw','text/html'),
  //~ special cases that can't be processed at all (some redirects)
  array('@.*@','@^libs/index.html$@i','raw','text/html'),
  array('@.*@','@^libs/bind/ref.html$@i','raw','text/html'),
  array('@.*@','@^libs/config/config.htm$@i','raw','text/html'),
  array('@.*@','@^libs/gil/doc/.*(html|htm)$@i','raw','text/html'),
  array('@.*@','@^libs/math/doc/common_factor.html$@i','raw','text/html'),
  array('@.*@','@^libs/preprocessor/doc/.*(html|htm)$@i','raw','text/html'),
  array('@.*@','@^libs/test/doc/components/test_tools/reference/.*(html|htm)$@i','raw','text/html'),
  array('@.*@','@^libs/python/doc/PyConDC_2003/bpl.html$@i','raw','text/html'),
  array('@.*@','@^libs/spirit/.*(html|htm)$@i','simple','text/html', 'add_spirit_analytics'),
  array('@.*@','@^libs/fusion/.*(html|htm)$@i','basic','text/html', 'add_spirit_analytics'),
  array('@.*@','@^libs/static_assert/static_assert.htm$@i','raw','text/html'),
  array('@.*@','@^libs/type_traits/cxx_type_traits.htm$@i','raw','text/html'),
  array('@.*@','@^libs/utility/iterator_adaptors.htm$@i','raw','text/html'),
  array('@.*@','@^libs/wave/.*(html|htm)$@i','raw','text/html'),
  array('@.*@','@^libs/range/doc/.*(html|htm)$@i','raw','text/html'),
  array('@.*@','@^status/index.html$@i','raw','text/html'),
  array('@.*@','@^tools/boostbook/index.html$@i','raw','text/html'),
  array('@.*@','@^tools/build/index.html$@i','raw','text/html'),
  array('@.*@','@^tools/jam/index.html$@i','raw','text/html'),
  array('@.*@','@^tools/quickbook/index.html$@i','raw','text/html'),
  array('@.*@','@^tools/regression/index.html?$@i','raw','text/html'),
  array('@.*@','@^wiki/index.html$@i','raw','text/html'),
  //~ special cases that can't be embeded in the standard frame
  array('@.*@','@^libs/iostreams/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/serialization/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/filesystem/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/system/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/numeric/conversion/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/optional/doc/.*(html|htm)$@i','simple','text/html'),
  //~ default to processed output for libs and tools
  array('@.*@','@^libs/[^/]+/doc/html/.*(html|htm)$@i','basic','text/html'),
  array('@.*@','@^libs/[^/]+/doc/[^/]+/html/.*(html|htm)$@i','basic','text/html'),
  array('@.*@','@^libs/[^/]+/doc/[^/]+/doc/html/.*(html|htm)$@i','basic','text/html'),
  array('@.*@','@^libs.*(html|htm)$@i','basic','text/html'),
  array('@.*@','@^tools.*(html|htm)$@i','basic','text/html'),
  array('@.*@','@^doc/html/.*html$@i','basic','text/html'),
  array('@.*@','@^more/.*html$@i','basic','text/html'),
  //~ the headers are text files displayed in an embeded page
  array('@.*@','@^boost/.*$@i','cpp','text/plain')
  ));

if ($_file->is_basic()) {
  print $_file->content();
}
else if (!$_file->is_raw()) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <?php $_file->content_head(); ?>
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href="/style/section-doc.css" />
  <!--[if IE 7]> <style type="text/css"> body { behavior: url(/style/csshover3.htc); } </style> <![endif]-->

</head><!-- <?php print $_file->file_; ?> -->

<body>
  <div id="heading">
    <?php virtual("/common/heading.html");?>
  </div>

  <div id="body">
    <div id="body-inner">
      <div id="content">
        <div class="section" id="docs">
          <div class="section-0">
            <div class="section-body">
              <?php $_file->content(); ?>
            </div>
          </div>
        </div>
      </div>

      <div class="clear"></div>
    </div>
  </div>

  <div id="footer">
    <div id="footer-left">
      <div id="revised">
        <p>Revised $Date$</p>
      </div>

      <div id="copyright">
        <p>Copyright Beman Dawes, David Abrahams, 1998-2005.</p>

        <p>Copyright Rene Rivera 2004-2008.</p>
      </div><?php virtual("/common/footer-license.html");?>
    </div>

    <div id="footer-right">
      <?php virtual("/common/footer-banners.html");?>
    </div>

    <div class="clear"></div>
  </div>
</body>
</html>
<?php } ?>
