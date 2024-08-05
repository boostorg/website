<?php
require_once(dirname(__FILE__) . '/../common/code/boost_irc_stats.php');

$_irc = new boost_irc_stats('http://www.acc.umu.se/~zao/stats/boost-soc.html');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <?php $_irc->content_head(); ?>
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href="/style-v2/section-community.css" />
  <script defer data-domain="original.boost.org" src="https://plausible.io/js/script.js"></script>
  <!--[if IE 7]> <style type="text/css"> body { behavior: url(/style-v2/csshover3.htc); } </style> <![endif]-->
</head>

<body>
  <div id="heading">
    <?php virtual("/common/heading.html"); ?>
  </div>

  <div id="body">
    <div id="body-inner">
      <div id="content">
        <div class="section" id="intro">
          <div class="section-0">
            <div class="section-title">
              <h1>Boost C++ Libraries - IRC Stats (#boost-soc)</h1>
            </div>

            <div class="section-body" id="irc-stats">
              <?php $_irc->content(); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="sidebar">
      <?php virtual("/common/sidebar-common.html"); ?><?php virtual("/common/sidebar-community.html"); ?>
    </div>

    <div class="clear"></div>
  </div>

  <div id="footer">
    <div id="footer-left">
      <div id="revised">
        <p>Revised $Date: 2007-07-28 01:11:14 -0500 (Sat, 28 Jul 2007) $</p>
      </div>

      <div id="copyright">
        <p>Copyright Rene Rivera 2007.</p>
      </div><?php virtual("/common/footer-license.html"); ?>
    </div>

    <div id="footer-right">
      <?php virtual("/common/footer-banners.html"); ?>
    </div>

    <div class="clear"></div>
  </div>
</body>
</html>
