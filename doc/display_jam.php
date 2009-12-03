<?php
require_once(dirname(__FILE__) . '/../common/code/boost_archive.php');

$_file = new boost_archive('@^[/]([^/]+)[/](.*)$@',$_SERVER["PATH_INFO"],array(
  //~ array(version-regex,path-regex,raw|simple|text|cpp|boost_book_html|boost_libs_html,mime-type[,preprocess hook]),
  array('@.*@','@[.](html|htm)$@i','boost_book_html','text/html'),
  ));

if (!$_file->is_raw()) { #~ require_once(dirname(__FILE__) . '/../common/code/webnotes.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <?php $_file->content_head(); ?>
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href="/style/section-doc.css" />
  <!--[if IE 7]> <style type="text/css"> body { behavior: url(/style/csshover3.htc); } </style> <![endif]-->
  <?php #~ pwn_head(); ?>

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

        <div class="section" id="notes">
          <div class="section-0">
            <div class="section-body">
              <?php #~ pwn_body($_file->key_,$_SERVER['PHP_SELF']); ?>
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

        <p>Copyright Rene Rivera 2004-2005.</p>
      </div><?php virtual("/common/footer-license.html");?>
    </div>

    <div id="footer-right">
      <?php virtual("/common/footer-banners.html");?>
    </div>

    <div class="clear"></div>
  </div> 
</body>
</html><?php } ?>
