<?php
require_once(dirname(__FILE__) . '/../common/code/boost_archive.php');

$_file = new boost_archive('@^[/]([^/]+)[/](.*)$@',$_SERVER["PATH_INFO"],array(
  //~ array(version-regex,path-regex,raw|simple|text|cpp|boost_book_html|boost_libs_html,mime-type),
  //~ special cases that can't be processed at all (mostly redirects)
  array('@1_(34)_[0-9]@','@^libs/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/algorithm/string/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/any/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/array/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/assign/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/bind/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/bind/ref.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/concept_check/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/config/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/date_time/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/date_time/doc/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/disjoint_sets/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/dynamic_bitset/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/filesystem/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/foreach/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/function/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/functional/hash/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/graph/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/io/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/iostreams/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/iterator/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/lambda/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/lambda/doc/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/math/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/math/doc/common_factor.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/math/doc/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/mem_fn/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/mpl/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/multi_array/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/multi_index/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/numeric/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/numeric/conversion/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/numeric/interval/doc/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/numeric/ublas/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/optional/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/parameter/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/pool/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/preprocessor/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/program_options/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/program_options/doc/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/property_map/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/ptr_container/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/python/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/python/doc/PyConDC_2003/bpl.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/python/doc/tutorial/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/python/doc/v2/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/regex/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/serialization/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/signals/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/signals/doc/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/smart_ptr/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/statechart/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/static_assert/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/static_assert/static_assert.htm$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/test/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/thread/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/thread/doc/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/tr1/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/tuple/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/typeof/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/type_traits/cxx_type_traits.htm$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/type_traits/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/utility/iterator_adaptors.htm$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/variant/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^libs/xpressive/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^more/getting_started.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^more/lib_guide.htm$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^more/regression.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^status/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^tools/build/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^tools/jam/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^tools/quickbook/index.html$@i','raw','text/html'),
  array('@1_(34)_[0-9]@','@^wiki/index.html$@i','raw','text/html'),
  //~ special cases that can't be embeded in the standard frame
  array('@.*@','@^libs/iostreams/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/preprocessor/doc/\.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/serialization/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/filesystem/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/numeric/conversion/doc/.*(html|htm)$@i','simple','text/html'),
  array('@.*@','@^libs/optional/doc/.*(html|htm)$@i','simple','text/html'),
  //~ default to processed output for libs and tools
  array('@.*@','@^libs.*(html|htm)$@i','boost_libs_html','text/html'),
  array('@.*@','@^tools.*(html|htm)$@i','boost_libs_html','text/html'),
  array('@.*@','@^doc/html/.*html$@i','boost_book_html','text/html'),
  //~ the headers are text files displayed in an embeded page
  array('@.*@','@^boost/.*$@i','cpp','text/plain')
  ));

if (!$_file->is_raw()) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <?php $_file->content_head(); ?>
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href="/style/section-doc.css" />
  <!--[if IE]> <style type="text/css"> body { behavior: url(/style/csshover.htc); } </style> <![endif]-->

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
  </div><?php } ?>
</body>
</html>
