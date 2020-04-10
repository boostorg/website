<?php

require_once(__DIR__.'/../common/code/bootstrap.php');

class PullRequestPage {
    static $param_defaults = Array(
        'page_view' => 'lib',
    );

    static $page_view_options = Array(
        'lib' => 'By Library',
        'date' => 'By Age',
    );

    var $params;            // Normalised URL parameters

    var $pull_requests;
    var $last_updated;

    var $page_url_path;     // URL path for this page
    var $page_view;         // Grouped by library, or sorted by age.

    function __construct($params) {
        $json_data = json_decode(
                file_get_contents(BOOST_DATA_DIR.'/pull-requests.json'));
        $this->pull_requests = $json_data->pull_requests;
        $this->last_updated = $json_data->last_updated;
        $this->page_url_path = preg_replace('![#?].*!', '', $_SERVER['REQUEST_URI']);

        $this->params = array();
        foreach (self::$param_defaults as $key => $default) {
            // Note: Using default for empty values as well as missing values.
            $this->params[$key] = strtolower(trim(
                BoostWebsite::array_get($params, $key))) ?: $default;
        }

        $this->page_view =  $this->params['page_view'];
        if (!array_key_exists($this->page_view, self::$page_view_options)) {
            BoostWeb::throw_http_error(400, 'Invalid view type',
                "Invalid view type: {$this->page_view}");
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

        echo '<p>Last updated ',
            $this->time_ago($this->last_updated),
            "</p>\n";

        switch ($this->page_view) {
            case 'lib':
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

            echo "<h2>", html_encode($name), "</h2>\n",
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
            "<a href='" . html_encode($pull->html_url) . "'>",
            ($name ? html_encode(preg_replace('@^boostorg/@', '', $name)).": " : ''),
            html_encode(rtrim($pull->title, '.')),
            "</a>",
            " (created: ",
            html_encode(gmdate("j M Y", strtotime($pull->created_at))),
            ", updated: ",
            html_encode(gmdate("j M Y", strtotime($pull->updated_at))),
            ")",
            "</li>\n";
    }

    function option_link($description, $field, $value) {
        $value = strtolower($value);
        $current_value = $this->params[$field];

        if ($current_value == $value) {
            echo '<span>', html_encode($description), '</span>';
        } else {
            $params = $this->params;
            $params[$field] = $value;

            $url_params = '';
            foreach ($params as $k => $v) {
                if ($v && $v !== self::$param_defaults[$k]) {
                    $url_params .= $url_params ? '&' : '?';
                    $url_params .= urlencode($k) . '=' . urlencode($v);
                }
            }

            echo '<a href="' . html_encode($this->page_url_path . $url_params) . '">',
            html_encode($description), '</a>';
        }
    }

    function time_ago($date, $now = null) {
        $date = new DateTime($date);
        $now = new DateTime($now ?: 'now');
        if ($date >= $now) {
            return ($date - $now <= 2) ? "just now" :
                "<i>in the future??? (probably an error somewhere)</i>";
        }
        $diff = date_diff($date, $now);
        $val = false;
        foreach(
            Array(
                'y' => 'year',
                'm' => 'month',
                'd' => 'day',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            ) as $member => $unit)
        {
            if ($val = $diff->{$member}) {
                return "{$val} {$unit}".($val != 1 ? 's' : '')." ago";
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
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href=
  "/style-v2/section-development.css" />
  <!--[if IE 7]> <style type="text/css"> body { behavior: url(/style-v2/csshover3.htc); } </style> <![endif]-->
</head><!--
Note: Editing website content is documented at:
https://www.boost.org/development/website_updating.html
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
