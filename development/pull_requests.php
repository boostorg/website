<?php

class PullRequestPage {
    var $pull_requests;

    function __construct($params) {
        $this->pull_requests = json_decode(
                file_get_contents(__DIR__ . '/../data/pull-requests.json'));
    }

    function display() {
        foreach ($this->pull_requests as $name => $repo_requests) {
            echo "<h2>", htmlentities($name), "</h2>\n",
            "<p>", htmlentities(count($repo_requests)),
            " open requests:</p>\n";
            foreach ($repo_requests as $pull) {
                echo "<li>",
                "<a href='" . htmlentities($pull->html_url) . "'>",
                htmlentities(rtrim($pull->title, '.')),
                "</a>",
                " (created: ",
                htmlentities(date("j M Y", $pull->created_at)),
                ", updated: ",
                htmlentities(date("j M Y", $pull->updated_at)),
                ")",
                "</li>\n";
            }
        }
    }
}

$page = new PullRequestPage($_GET);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title>Open Pull Requests - Boost C++ Libraries</title>
  <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href=
  "/style-v2/section-development.css" />
  <!--[if IE 7]> <style type="text/css"> body { behavior: url(/style-v2/csshover3.htc); } </style> <![endif]-->
</head><!--
Note: Editing website content is documented at:
http://www.boost.org/development/website_updating.html
-->

<body>
  <div id="heading">
    <?php virtual("/common/heading.html") ?>
  </div>

  <div id="body">
    <div id="body-inner">
      <div id="content">
        <div class="section" id="intro">
          <div class="section-0">
            <div class="section-title">
              <h1>Open Pull Requests</h1>
            </div>

            <div class="section-body">
              <?php $page->display(); ?>
            </div>
          </div>
        </div>
      </div>

      <div id="sidebar">
        <?php virtual("/common/sidebar-common.html") ?>
        <?php virtual("/common/sidebar-development.html") ?>
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
        <p>Copyright Daniel James 2014.</p>
      </div><?php virtual("/common/footer-license.html") ?>
    </div>

    <div id="footer-right">
      <?php virtual("/common/footer-banners.html") ?>
    </div>

    <div class="clear"></div>
  </div>
</body>
</html>
