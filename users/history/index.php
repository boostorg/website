<?php
require_once(dirname(__FILE__) . '/../../common/code/boost_feed.php');
$_history = new boost_feed(dirname(__FILE__) . '/../../feed/history.rss', '/users/history');
$_history->sort_by('pubdate');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title>Boost Version History</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
              <h1>Boost Version History</h1>
            </div>

            <div class="section-body">
              <ul class="toc">
                <?php foreach ( $_history->db as $_guid => $_item ) { ?>

                <li><span class=
                "news-title"><?php print '<a href="#i'.$_item['guid'].'">'; ?><?php print $_item['title']; ?><?php print '</a>'; ?></span></li><?php } ?>
              </ul><?php foreach ( $_history->db as $_guid => $_item ) { ?>

              <h2 class="news-title">
              <?php print '<a name="i'.$_item['guid'].'" id="i'.$_item['guid'].'"></a><a href="'.$_item['link'].'">'; ?><?php print $_item['title']; ?><?php print '</a>'; ?></h2>

              <p class="news-date"><?php print $_item['date']; ?></p>

              <div class="news-description">
                <?php print $_item['boostbook:purpose']; ?>
              </div>

              <ul class="menu">
                <li>
                <?php print '<a href="'.$_item['link'].'">Details</a>'; ?></li>

                <?php if($_item['boostbook:download']) : ?>
                <li><?php print '<a href="'.$_item['boostbook:download'].'">Download</a>'; ?></li>
                <?php endif; ?>
              </ul><?php } ?>
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
        <p>Copyright Rene Rivera 2006-2007.</p>
      </div><?php virtual("/common/footer-license.html"); ?>
    </div>

    <div id="footer-right">
      <?php virtual("/common/footer-banners.html"); ?>
    </div>

    <div class="clear"></div>
  </div>
</body>
</html>
