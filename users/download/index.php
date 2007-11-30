<?php
require_once(dirname(__FILE__) . '/../../common/code/boost_feed.php');
$_downloads = new boost_feed(dirname(__FILE__) . '/../../feed/downloads.rss', '/users/download');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title>Boost Downloads</title>
  <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href="/style/section-boost.css" />
  <!--[if IE]> <style type="text/css"> body { behavior: url(/style/csshover.htc); } </style> <![endif]-->
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
              <h1>Boost Downloads</h1>
            </div>

            <div class="section-body">
              <ul class="toc">
                <?php foreach ( $_downloads->db as $_guid => $_item ) { ?>

                <li><span class=
                "news-title"><?php print '<a href="#'.$_item['guid'].'">'; ?><?php print $_item['title']; ?><?php print '</a>'; ?></span></li><?php } ?>
              </ul><?php foreach ( $_downloads->db as $_guid => $_item ) { ?>

              <h2><span class=
              "news-title"><?php print '<a name="'.$_item['guid'].'" id="'.$_item['guid'].'"></a><a href="'.$_item['link'].'">'; ?><?php print $_item['title']; ?><?php print '</a>'; ?></span></h2>

              <p class="news-date"><?php print $_item['date']; ?></p>

              <div class="news-description">
                <?php print $_item['boostbook:purpose']; ?>
              </div><?php } ?>
            </div>
          </div>
        </div>
      </div>

      <div id="sidebar">
        <?php virtual("/common/sidebar-common.html"); ?><?php virtual("/common/sidebar-boost.html"); ?>
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
        <p>Copyright Rene Rivera 2006.</p>
      </div><?php virtual("/common/footer-license.html"); ?>
    </div>

    <div id="footer-right">
      <?php virtual("/common/footer-banners.html"); ?>
    </div>

    <div class="clear"></div>
  </div>
</body>
</html>
