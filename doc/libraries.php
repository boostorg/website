<?php

require_once(dirname(__FILE__) . '/../common/code/boost.php');

class LibraryPage {
    static $view_fields = Array(
        '' => 'All',
        'categorized' => 'Categorized'
    );

    static $filter_fields = Array(
        'std-proposal' => 'Standard Proposals',
        'std-tr1' => 'TR1 libraries',
        'header-only' => '[old]',
        'autolink' => '[old]'
    );

    static $sort_fields =  Array(
        'name' => 'Name',
        'boost-version' => 'First Release',
        'std-proposal' => 'STD Proposal',
        'std-tr1' => 'STD::TR1',
        'key' => 'Key'
    );

    static $display_sort_fields = Array(
        '' => 'Name',
        'boost-version' => 'First Release'
    );

    var $params = array();
    var $libs;
    var $categories;

    var $base_uri = '';
    var $view_value = '';
    var $category_value = '';
    var $filter_value = '';
    var $sort_value = 'name';
    var $attribute_filter = false;

    function __construct($params, $libs) {
        $this->libs = $libs;
        $this->categories = $libs->get_categories();

        $base_uri = $_SERVER['REQUEST_URI'];
        $base_uri = preg_replace('@[#?].*@', '', $base_uri);
        $base_uri = preg_replace('@//+@', '/', $base_uri);
        $this->base_uri = $base_uri;

        if (array_key_exists('view', $params)) {
            $view_value = $params['view'];
            $filter_value = '';

            if (!preg_match('@^[-_a-zA-Z0-9]+$@', $view_value)) {
                die('Invalid view value.');
            }

            if (strpos($view_value, 'filtered_') === 0) {
                $filter_value = substr($view_value, strlen('filtered_'));
                if (!array_key_exists($filter_value, self::$filter_fields)) {
                    echo 'Invalid filter field.'; exit(0);
                }
                if (self::$filter_fields[$filter_value] == '[old]') {
                    echo 'Filter field no longer supported.'; exit(0);
                }
            }
            else if (strpos($view_value, 'category_') === 0) {
                $this->category_value = substr($view_value, strlen('category_'));
                if(!array_key_exists($this->category_value, $this->categories)) {
                    echo 'Invalid category: '.html_encode($this->category_value); exit(0);
                }
            }
            else {
                if (!array_key_exists($view_value, self::$view_fields)) {
                    echo 'Invalid view value.'; exit(0);
                }
            }

            $this->view_value = $view_value;
            $this->filter_value = $filter_value;
        }

        if (array_key_exists('sort', $params)) {
            $sort_value = $params['sort'];

            if (!preg_match('@^[-_a-zA-Z0-9]+$@', $sort_value)) {
                die('Invalid sort field.');
            }

            if (!array_key_exists($sort_value, self::$sort_fields)) {
                echo 'Invalid sort field.'; exit(0);
            }

            $this->sort_value = $sort_value;
        }

        if (!empty($params['filter'])) {
            $attribute_filter = $params['filter'];

            if (!preg_match('@^[-_a-zA-Z0-9]+$@', $attribute_filter)) {
                die('Invalid attribute filter.');
            }

            $this->attribute_filter = $attribute_filter;
        }

        // Store the sanitized parameters for quickly generating links later.
        $this->params = array(
            'view' => $this->view_value,
            'sort' => $this->sort_value,
            'sort' => $this->attribute_filter,
        );
    }

    function filter($lib) {
        if (BoostVersion::page()->is_numbered_release()
                && !$lib['boost-version']->is_release()) {
            return false;
        }

        if ($this->filter_value && empty($lib[$this->filter_value])) {
            return false;
        }

        if ($this->attribute_filter && empty($lib[$this->attribute_filter])) {
            return false;
        }

        if ($this->category_value && (empty($lib['category']) ||
                !in_array($this->category_value, $lib['category']))) {
            return false;
        }

        return true;
    }

    function title() {
        $page_title = BoostVersion::page_title().' Library Documentation';
        if ($this->category_value) {
            $page_title.= ' - '. $this->categories[$this->category_value]['title'];
        }

        return $page_title;
    }

    function category_subtitle() {
        if($this->category_value) {
            echo '<h2>',
                html_encode($this->categories[$this->category_value]['title']),
                '</h2>';
        }
    }

    function view_menu_items() {
        foreach (self::$view_fields as $key => $description) {
            echo '<li>';
            $this->option_link($description, 'view', $key);
            echo '</li> ';
        }
    }

    function filter_menu_items() {
        foreach (self::$filter_fields as $key => $description) {
            if (!preg_match('@^\[.*\]$@', $description)) {
                echo '<li>';
                $this->option_link($description, 'view', 'filtered_'.$key);
                echo '</li> ';
            }
        }
    }

    function sort_menu_items() {
        foreach (self::$display_sort_fields as $key => $description) {
            echo '<li>';
            $this->option_link($description, 'sort', $key);
            echo '</li> ';
        }
    }

    function filtered_libraries() {
        return $this->libs->get_for_version(BoostVersion::page(),
                $this->sort_value,
                array($this, 'filter'));
    }

    function categorized_libraries() {
        return $this->libs->get_categorized_for_version(BoostVersion::page(),
                $this->sort_value,
                array($this, 'filter'));
    }

    // Library display functions:

    function libref($lib) {
        if (!empty($lib['documentation'])) {
            $path_info = filter_input(INPUT_SERVER, 'PATH_INFO', FILTER_SANITIZE_URL);
            if ($path_info && $path_info != '/') {
                $docref = '/doc/libs' . $path_info . '/' . $lib['documentation'];
            } else {
                $docref = '/doc/libs/release/' . $lib['documentation'];
            }
            print '<a href="' . html_encode($docref) . '">' .
                    html_encode(!empty($lib['name']) ? $lib['name'] : $lib['key']) .
                    '</a>';
        } else {
            print html_encode(!empty($lib['name']) ? $lib['name'] : $lib['key']);
        }

        if (!empty($lib['status'])) {
            print ' <em>(' . html_encode($lib['status']) . ')</em>';
        }
    }

    function libdescription($lib) {
        echo !empty($lib['description']) ?
                html_encode($lib['description'],ENT_NOQUOTES,'UTF-8') :
                '&nbsp;';
    }

    function libauthors($lib) {
        print !empty($lib['authors']) ?
                html_encode($lib['authors']) : '&nbsp;';
    }

    function libavailable($lib) {
        print $lib['boost-version']->is_release() ?
            html_encode($lib['boost-version']) :
            '<i>'.html_encode($lib['boost-version']).'</i>';
    }

    function libstandard($lib) {
        $p = array();
        if ($lib['std-proposal']) {
            $p[] = 'Proposed';
        }
        if ($lib['std-tr1']) {
            $p[] = 'TR1';
        }
        print ($p ? implode(', ', $p) : '&nbsp;');
    }

    function libcategories($lib) {
        $first = true;
        if ($lib['category']) {
            foreach ($lib['category'] as $category_name) {
                if (!$first)
                    echo ', ';
                $first = false;
                $this->category_link($category_name);
            }
        }
        if ($first)
            echo '&nbsp;';
    }

    function option_link($description, $field, $value) {
        if (!array_key_exists($field, $this->params)) {
            die("Invalid field: ".html_encode($field));
        }
        $current_value = $this->params[$field];

        if ($current_value == $value) {
            echo '<span>', html_encode($description), '</span>';
        } else {
            $params = $this->params;
            $params[$field] = $value;

            $url_params = '';
            foreach ($params as $k => $v) {
                if (is_string($v) && $v != '') {
                    $url_params .= $url_params ? '&' : '?';
                    $url_params .= urlencode($k) . '=' . urlencode($v);
                }
            }

            echo '<a href="' . html_encode($this->base_uri . $url_params) . '">',
            html_encode($description), '</a>';
        }
    }

    function category_link($name) {
        $category = $this->categories[$name];
        $this->option_link(
                isset($category['title']) ? $category['title'] : $name,
                'view', 'category_' . $name);
    }
}

// Page variables

$library_page = new LibraryPage($_GET, BoostLibraries::load());

if (BoostVersion::page()->is_numbered_release() &&
        $library_page->libs->latest_version &&
        BoostVersion::page()->compare($library_page->libs->latest_version) > 0)
{
    BoostWeb::error_404($_SERVER['REQUEST_URI']);
    return;
}

// To avoid confusion, only show this page when there is actual documentation.
// TODO: Maybe for versions without documentation, could display the list
//       with no links.
// TODO: This duplicates the BoostDocumentation object in display_libs.
$archive = new BoostDocumentation(array(
    'fix_dir' => dirname(__FILE__).'/fixes',
    'archive_dir' => STATIC_DIR,
    'use_http_expire_date' => true,
));

if (!is_dir($archive->documentation_dir()))
{
    BoostWeb::error_404($_SERVER['REQUEST_URI']);
    return;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title><?php echo html_encode($library_page->title()); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href="/style-v2/section-doc.css" />
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
              <h1><?php echo html_encode($library_page->title()); ?></h1>
            </div>

            <div class="section-body">
              <div id="options">
                  <div id="view-options">
                    <ul class="menu">
                    <?php $library_page->view_menu_items(); ?>
                    <?php $library_page->filter_menu_items(); ?>
                    </ul>
                  </div>
                  <div id="sort-options">
                    <div class="label">Sort by:</div>
                    <ul class="menu">
                    <?php $library_page->sort_menu_items(); ?>
                    </ul>
                  </div>
              </div>

              <?php if ($library_page->view_value != 'categorized'): ?>

              <?php $library_page->category_subtitle(); ?>
              <dl>
              <?php foreach ($library_page->filtered_libraries() as $lib): ?>
                <dt><?php $library_page->libref($lib); ?></dt>
                <dd>
                  <p><?php $library_page->libdescription($lib); ?></p>
                  <dl class="fields">
                    <dt>Author(s)</dt>
                    <dd><?php $library_page->libauthors($lib); ?></dd>
                    <dt>First&nbsp;Release</dt>
                    <dd><?php $library_page->libavailable($lib); ?></dd>
                    <dt>Standard</dt>
                    <dd><?php $library_page->libstandard($lib); ?></dd>
                    <dt>Categories</dt>
                    <dd><?php $library_page->libcategories($lib); ?></dd>
                  </dl>
                </dd>
              <?php endforeach; ?>
              </dl>

              <?php else: ?>

              <h2>By Category</h2>
              <?php
              foreach ($library_page->categorized_libraries() as $name => $category) {
                if(count($category['libraries'])) {
                  echo '<h3>';
                  $library_page->category_link($name);
                  echo '</h3>';
                  echo '<ul>';
                  foreach ($category['libraries'] as $lib) {
                    echo '<li>';
                    $library_page->libref($lib);
                    echo ': ';
                    $library_page->libdescription($lib);
                    echo '</li>';
                  }
                  echo '</ul>';
                }
              }
              ?>

              <?php endif ?>
            </div>
          </div>
        </div>
      </div>

      <div id="sidebar">
        <?php virtual("/common/sidebar-common.html"); ?><?php virtual("/common/sidebar-doc.html"); ?>
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
      </div><?php virtual("/common/footer-license.html"); ?>
    </div>

    <div id="footer-right">
      <?php virtual("/common/footer-banners.html"); ?>
    </div>

    <div class="clear"></div>
  </div>
</body>
</html>
