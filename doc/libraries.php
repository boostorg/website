<?php

// Change this when developing.
define('USE_SERIALIZED_INFO', true);

require_once(dirname(__FILE__) . '/../common/code/boost.php');
require_once(dirname(__FILE__) . '/../common/code/boost_libraries.php');

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

    var $libs;
    var $categories;

    var $view_value = '';
    var $category_value = '';
    var $filter_value = '';
    var $sort_value = 'name';
    var $attribute_filter = false;

    function __construct($params, $libs) {
        $this->libs = $libs;
        $this->categories = $libs->get_categories();

        if (isset($params['view'])) { $this->view_value = $params['view']; }

        if (strpos($this->view_value, 'filtered_') === 0) {
            $this->filter_value = substr($this->view_value, strlen('filtered_'));
            if (!isset(self::$filter_fields[$this->filter_value])) {
                echo 'Invalid filter field.'; exit(0);
            }
            if (self::$filter_fields[$this->filter_value] == '[old]') {
                echo 'Filter field no longer supported.'; exit(0);
            }
        }
        else if (strpos($this->view_value, 'category_') === 0) {
            $this->category_value = substr($this->view_value, strlen('category_'));
            if(!isset($this->categories[$this->category_value])) {
                echo 'Invalid category: '.htmlentities($this->category_value); exit(0);
            }
        }
        else {
            $this->filter_value = '';
            if (!isset(self::$view_fields[$this->view_value])) {
                echo 'Invalid view value.'; exit(0);
            }
        }

        if (!empty($params['sort'])) {
            $this->sort_value = $params['sort'];

            if (!isset(self::$sort_fields[$this->sort_value])) {
                echo 'Invalid sort field.'; exit(0);
            }
        }

        if (!empty($params['filter'])) {
            $this->attribute_filter = $params['filter'];
        }
    }

    function filter($lib) {
        return (!$this->filter_value || $lib[$this->filter_value]) &&
            (!$this->attribute_filter || $lib[$this->attribute_filter]) &&
            (!$this->category_value ||
                array_search($this->category_value, $lib['category']) !== FALSE);
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
                htmlentities($this->categories[$this->category_value]['title']),
                '</h2>';
        }
    }

    function view_menu_items() {
        foreach (self::$view_fields as $key => $description) {
            echo '<li>';
            option_link($description, 'view', $key);
            echo '</li> ';
        }
    }

    function filter_menu_items() {
        foreach (self::$filter_fields as $key => $description) {
            if (!preg_match('@^\[.*\]$@', $description)) {
                echo '<li>';
                option_link($description, 'view', 'filtered_'.$key);
                echo '</li> ';
            }
        }
    }

    function sort_menu_items() {
        foreach (self::$display_sort_fields as $key => $description) {
            echo '<li>';
            option_link($description, 'sort', $key);
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
}

// Library display functions:

function libref($lib)
{
  if (!empty($lib['documentation']))
  {
    $path_info = filter_input(INPUT_SERVER, 'PATH_INFO', FILTER_SANITIZE_URL);
    if ($path_info && $path_info != '/')
    {
      $docref = '/doc/libs'.$path_info.'/'.$lib['documentation'];
    }
    else
    {
      $docref = '/doc/libs/release/'.$lib['documentation'];
    }
    print '<a href="'.$docref.'">'.($lib['name'] ?: $lib['key']).'</a>';
  }
  else
  {
    print ($lib['name'] ?: $lib['key']);
  }

  if (!empty($lib['status']))
  {
      print ' <em>('.htmlentities($lib['status']).')</em>';
  }
}
function libauthors($lib)
{
  print ($lib['authors'] ?: '&nbsp;');
}
function libavailable($lib)
{
  print ($lib['boost-version'] ?: '&nbsp;');
}
function libstandard($lib)
{
  $p = array();
  if ($lib['std-proposal']) { $p[] = 'Proposed'; }
  if ($lib['std-tr1']) { $p[] = 'TR1'; }
  print ($p ? implode(', ',$p) : '&nbsp;');
}
function libcategories($lib, $categories)
{
  $first = true;
  if($lib['category']) {
    foreach($lib['category'] as $category_name) {
      if(!$first) echo ', ';
      $first = false;
      category_link($category_name, $categories[$category_name]);
    }
  }
  if($first) echo '&nbsp;';
}

function option_link($description, $field, $value)
{
  $base_uri = preg_replace('![#?].*!', '', $_SERVER['REQUEST_URI']);
  $current_value = isset($_GET[$field]) ? $_GET[$field] : '';

  if($current_value == $value) {
    echo '<span>',htmlentities($description), '</span>';
  }
  else {
    $params = $_GET;
    $params[$field] = $value;

    $url_params = '';
    foreach($params as $k => $v) {
      if($v) {
        $url_params .= $url_params ? '&' : '?';
        $url_params .= urlencode($k) . '='. urlencode($v);
      }
    }

    echo '<a href="'.htmlentities($base_uri.$url_params).'">',
          htmlentities($description), '</a>';
  }
}

function category_link($name, $category) {
  option_link(
    isset($category['title']) ? $category['title'] : $name,
    'view', 'category_'.$name);
}

// Page variables

$library_page = new LibraryPage($_GET,
    USE_SERIALIZED_INFO ?
	unserialize(file_get_contents(dirname(__FILE__) . '/../generated/libraries.txt')) :
	boost_libraries::from_xml_file(dirname(__FILE__) . '/libraries.xml'));

$categories = $library_page->categories;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title><?php echo htmlentities($library_page->title()); ?></title>
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
              <h1><?php echo htmlentities($library_page->title()); ?></h1>
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

              <?php if ($library_page->view_value != 'categorized') { ?>

              <?php $library_page->category_subtitle(); ?>

              <dl>
                <?php
                foreach ($library_page->filtered_libraries() as $lib) { ?>

                <dt><?php libref($lib); ?></dt>

                <dd>
                  <p>
                  <?php echo ($lib['description'] ? htmlentities($lib['description'],ENT_NOQUOTES,'UTF-8') : '&nbsp;'); ?></p>

                  <dl class="fields">
                    <dt>Author(s)</dt>

                    <dd><?php libauthors($lib); ?></dd>

                    <dt>First&nbsp;Release</dt>

                    <dd><?php libavailable($lib); ?></dd>

                    <dt>Standard</dt>

                    <dd><?php libstandard($lib); ?></dd>

                    <dt>Categories</dt>

                    <dd><?php libcategories($lib, $categories); ?></dd>
                  </dl>
                </dd><!-- --><?php } ?>
              </dl>

              <?php } else { ?>

              <h2>By Category</h2>
              <?php
              foreach ($library_page->categorized_libraries() as $name => $category) {
                if(count($category['libraries'])) {?>
                  <h3><?php category_link($name, $category); ?></h3>
                  <ul><?php foreach ($category['libraries'] as $lib) { ?>
                    <li><?php libref($lib); ?>: <?php echo ($lib['description'] ? htmlentities($lib['description'],ENT_NOQUOTES,'UTF-8') : '&nbsp;'); ?></li>
                  <?php } ?></ul>
                <?php } ?>
              <?php } ?>

              <?php } ?>
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
