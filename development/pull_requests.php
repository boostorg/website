<?php

class PullRequestPage {
    static $page_view_options = Array(
        '' => 'By Library',
        'date' => 'By Age',
    );

    var $pull_requests;
    var $base_uri;
    var $params;
    var $page_view;

    function __construct($params) {
        $this->pull_requests = json_decode(
                file_get_contents(__DIR__ . '/../data/pull-requests.json'));
        $this->base_uri = preg_replace('![#?].*!', '', $_SERVER['REQUEST_URI']);
        $this->params = $params;
        if (isset($params['page_view'])) {
            $this->page_view =  $params['page_view'];
        }
    }

    function display() {
        echo '<div id="options">';
        echo '<div id="view-options">';
        echo '<ul class="menu">';
        foreach (self::$page_view_options as $key => $description) {
            echo '<li>';
            $this->option_link($description, 'page_view', $key);
            echo '</li> ';
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';

        switch ($this->page_view) {
            case '':
                $this->by_library();
                break;
            case 'date':
                $this->by_date();
                break;
            default:
                echo "Invalid page_view.";
        }
    }

    function by_library() {
        foreach ($this->pull_requests as $name => $repo_requests) {
            $repo_count = count($repo_requests);

            echo "<h2>", htmlentities($name), "</h2>\n",
                "<p> {$repo_count} open request",
                ($repo_count != 1 ? 's' : ''),
                ":</p>\n";
            foreach ($repo_requests as $pull) {
                $this->pull_request_item($pull);
            }
        }
    }

    function by_date() {
        $pull_requests = Array();
        foreach ($this->pull_requests as $name => $repo_requests) {
            foreach ($repo_requests as $pull) {
                $pull->name = $name;
                $pull_requests[] = $pull;
            }
        }

        usort($pull_requests, function($x, $y) {
            return strtotime($x->created_at) - strtotime($y->created_at);
        });

        echo '<ul>';
        foreach ($pull_requests as $pull) {
            $this->pull_request_item($pull, $pull->name);
        }
        echo "</ul>\n";
    }

    function pull_request_item($pull, $name = null) {
        echo "<li>",
            "<a href='" . htmlentities($pull->html_url) . "'>",
            ($name ? htmlentities($name).": " : ''),
            htmlentities(rtrim($pull->title, '.')),
            "</a>",
            " (created: ",
            htmlentities(date("j M Y", strtotime($pull->created_at))),
            ", updated: ",
            htmlentities(date("j M Y", strtotime($pull->updated_at))),
            ")",
            "</li>\n";
    }

    function option_link($description, $field, $value) {
        $current_value = isset($this->params[$field]) ? $this->params[$field] : '';

        if ($current_value == $value) {
            echo '<span>', htmlentities($description), '</span>';
        } else {
            $params = $this->params;
            $params[$field] = $value;

            $url_params = '';
            foreach ($params as $k => $v) {
                if ($v) {
                    $url_params .= $url_params ? '&' : '?';
                    $url_params .= urlencode($k) . '=' . urlencode($v);
                }
            }

            echo '<a href="' . htmlentities($this->base_uri . $url_params) . '">',
            htmlentities($description), '</a>';
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
