<?php
require_once(dirname(__FILE__) . '/common/code/boost_feed.php');
$_news = new boost_feed(dirname(__FILE__) . '/feed/news.rss', '/users/news');
$_news->sort_by('pubdate');
$_downloads = new boost_feed(dirname(__FILE__) . '/feed/downloads.rss', '/users/download');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <meta name="generator" content=
  "HTML Tidy for Windows (vers 1st November 2003), see www.w3.org" />

  <title>Boost C++ Libraries</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href="/style/section-welcome.css" />
  <!--[if IE]> <style type="text/css"> body { behavior: url(/style/csshover.htc); } </style> <![endif]-->
</head>

<!--
Note: Editing website content is documented at:
http://www.boost.org/development/website_updating.html
-->

<body>
  <div id="heading">
    <?php virtual("/common/heading.html"); ?>
  </div>

  <div id="body">
    <div id="body-inner">
      <div id="content">
        <div class="section" id="intro">
          <div class="section-0">
            <div class="section-body">
              <div class="directory">
                <div class="directory-item" id="welcome">
                  <h2>Welcome to Boost.org!</h2>

                  <p>Boost provides free peer-reviewed portable C++ source
                  libraries.</p>

                  <p>We emphasize libraries that work well with the C++
                  Standard Library. Boost libraries are intended to be widely
                  useful, and usable across a broad spectrum of applications.
                  The <a href="/users/license.html">Boost license</a>
                  encourages both commercial and non-commercial use.</p>

                  <p>We aim to establish "existing practice" and provide
                  reference implementations so that Boost libraries are
                  suitable for eventual standardization. Ten Boost libraries
                  are already included in the <a href=
                  "http://www.open-std.org/jtc1/sc22/wg21/" class=
                  "external">C++ Standards Committee's</a> Library Technical
                  Report (<a href=
                  "http://www.open-std.org/jtc1/sc22/wg21/docs/papers/2005/n1745.pdf"
                  class="external">TR1</a>) as a step toward becoming part of
                  a future C++ Standard. More Boost libraries are proposed
                  for the upcoming <a href=
                  "http://www.open-std.org/jtc1/sc22/wg21/docs/papers/2005/n1810.html"
                  class="external">TR2</a>.</p>

                  <h3 class="note">Getting Started</h3>

                  <p class="note"><span class="note-body">Boost works on
                  almost any modern operating system, including UNIX and
                  Windows variants. Follow the <a href=
                  "/doc/libs/release/more/getting_started/index.html">Getting
                  Started Guide</a> to download and install Boost. Popular
                  Linux and Unix distributions such as <a href=
                  "http://fedoraproject.org/" class="external">Fedora</a>,
                  <a href="http://www.debian.org/" class=
                  "external">Debian</a>, and <a href="http://www.netbsd.org/"
                  class="external">NetBSD</a> include pre-built Boost
                  packages. Boost may also already be available on your
                  organization's internal web server.</span></p>

                  <h3 class="note">Background</h3>

                  <p class="note"><span class="note-body">Read on with the
                  <a href="/users/">introductory material</a> to help you
                  understand what Boost is about and to help in educating
                  your organization about Boost.</span></p>

                  <h3 class="note">Community</h3>

                  <p class="note"><span class="note-body">Boost welcomes and
                  thrives on participation from a variety of individuals and
                  organizations. Many avenues for participation are available
                  in the <a href="/community/">Boost
                  Community</a>.</span></p>
                </div>

                <div class="directory-item" id="important-downloads">
                  <h2>Downloads</h2>

                  <ul id="downloads">
                    <?php $_count = 0; foreach ( $_downloads->db as $_guid => $_item ) { $_count += 1; if ($_count > 5) { break; } ?>

                    <li><span class=
                    "news-title"><?php print '<a href="'.$_item['link'].'">'; ?><?php print $_item['title']; ?><?php print '</a>'; ?></span>
                    <span class=
                    "news-date"><?php print $_item['date']; ?></span></li><?php } ?>
                  </ul>

                  <p><a href="/users/download/">More Downloads...</a>
                  (<a href="feed/downloads.rss">RSS</a>)</p>
                </div>

                <div class="directory-item" id="important-news">
                  <h2>News</h2>

                  <ul id="news">
                    <?php $_count = 0; foreach ( $_news->db as $_guid => $_item ) { $_count += 1; if ($_count > 3) { break; } ?>

                    <li><span class=
                    "news-title"><?php print '<a href="'.$_item['link'].'">'; ?><?php print $_item['title']; ?><?php print '</a>'; ?></span>
                    <span class=
                    "news-description"><?php print $_item['boostbook:purpose']; ?></span>
                    <span class=
                    "news-date"><?php print $_item['date']; ?></span></li><?php } ?>
                  </ul>

                  <p><a href="/users/news/">More News...</a> (<a href=
                  "feed/news.rss">RSS</a>)</p>
                </div>

                <div class="clear"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div id="sidebar">
        <?php virtual("/common/sidebar-common.html"); ?><?php virtual("/common/sidebar-welcome.html"); ?>
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

        <p>Copyright Rene Rivera 2004-2007.</p>
      </div><?php virtual("/common/footer-license.html"); ?>
    </div>

    <div id="footer-right">
      <?php virtual("/common/footer-banners.html"); ?>
    </div>

    <div class="clear"></div>
  </div>
</body>
</html>
