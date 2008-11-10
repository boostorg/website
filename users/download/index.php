<?php
require_once(dirname(__FILE__) . '/../../common/code/boost_feed.php');
$_downloads = new boost_feed(dirname(__FILE__) . '/../../feed/downloads.rss', '/users/download');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title>Boost Downloads</title>
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
              <h1>Boost Downloads</h1>
            </div>

            <div class="section-body">
              <ul class="toc">
                <li><a href="#releases">Packaged Releases</a></li>

                <li><a href="#repository">Subversion Repository</a></li>
              </ul>

              <h2><a name="releases" id="releases"></a>Packaged
              Releases</h2><?php foreach ( $_downloads->db as $_guid => $_item ) { ?>

              <h3><span class=
              "news-title"><?php print $_item['title']; ?></span></h3>

              <p class="news-date"><?php print $_item['date']; ?></p>

              <p class="news-description">
              <?php print $_item['boostbook:purpose']; ?></p>

              <ul class="menu">
                <li>
                <?php print '<a href="'.htmlentities($_item['link']).'">Details</a>'; ?></li>

                <li>
                <?php print '<a href="'.htmlentities($_item['boostbook:download']).'">Download</a>'; ?></li>
              </ul><?php } ?>

              <h2><a name="repository" id="repository"></a>Subversion
              Repository</h2>

              <p>Boost uses <a class="external" href=
              "http://subversion.tigris.org/">Subversion</a> to manage all of
              the data associated with Boost's development, including the
              source code to Boost, documentation for Boost libraries, and
              the Boost web site.</p>

              <h3>Accessing the Boost Subversion Repository</h3>

              <p>The Subversion repository can be accessed in several
              ways:</p>

              <ul>
                <li>Anonymous, read-only access to the Boost Subversion
                repository is available at <a href=
                "http://svn.boost.org/svn/boost">http://svn.boost.org/svn/boost</a>.
                To access the current Boost development code, for instance,
                one would check out from <a href=
                "http://svn.boost.org/svn/boost/trunk">http://svn.boost.org/svn/boost/trunk</a>.
                For example, using the command-line <tt>svn</tt>, one might
                use:
                  <pre>
svn co <a href=
"http://svn.boost.org/svn/boost/trunk">http://svn.boost.org/svn/boost/trunk</a> boost-trunk
</pre>
                </li>

                <li>The Subversion repository can be browsed online at
                <a href=
                "http://svn.boost.org/trac/boost/browser">http://svn.boost.org/trac/boost/browser</a>.</li>

                <li>On Windows, <a href=
                "http://tortoisesvn.tigris.org/">TortoiseSVN</a> provides an
                easy-to-use, graphical interface to Subversion.</li>
              </ul>

              <h3>Organization of the Boost Subversion Repository</h3>

              <p>The Boost Subversion repository is organized into several
              top-level directories, reflecting various stages of Boost
              library development and subtasks within the Boost community. We
              have the following top-level directories:</p>

              <ul>
                <li><tt>trunk</tt>: Contains the latest "development" version
                of Boost.</li>

                <li><tt>sandbox</tt>: Contains libraries and tools that are
                under active development and have not yet been reviewed or
                accepted into Boost. See <a href=
                "http://svn.boost.org/trac/boost/wiki/BoostSandbox">BoostSandbox</a>
                for information about organization of the sandbox.</li>

                <li><tt>website</tt>: Contains the upcoming Boost web site,
                which is not yet live.</li>

                <li><tt>branches</tt>: Contains various branches of Boost
                libraries, typically for release branches and for non-trivial
                changes to Boost libraries that need to be made separately
                from the trunk.</li>

                <li><tt>tags</tt>: Contains "tags" that mark certain points
                in the source tree, such as particular Boost releases.</li>
              </ul>
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
