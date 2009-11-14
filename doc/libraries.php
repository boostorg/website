<?php

require_once(dirname(__FILE__) . '/../common/code/boost_version.php');
require_once(dirname(__FILE__) . '/../common/code/boost_libraries.php');

$libs = new boost_libraries(dirname(__FILE__) . '/libraries.xml');

// Display types:

$view_fields = Array(
    '' => 'All',
    'categorized' => 'Categorized'
);
$filter_fields = Array(
    'std-proposal' => 'Standard Proposals',
    'std-tr1' => 'TR1 libraries',
    'header-only' => 'Header Only Libraries',
    'autolink' => 'Automatic Linking');
$sort_fields =  Array(
    'name' => 'Name',
    'boost-version' => 'First Release',
    'std-proposal' => 'STD Proposal',
    'std-tr1' => 'STD::TR1',
    'header-only' => 'Header Only Use',
    'autolink' => 'Automatic Linking',
    'key' => 'Key'
);
$display_sort_fields = Array(
    '' => 'Name',
    'boost-version' => 'First Release'
);

// View

$view_value = isset($_GET['view']) ? $_GET['view'] : '';

$category_value = '';
$filter_value = '';

if(strpos($view_value, 'filtered_') === 0) {
    $filter_value = substr($view_value, strlen('filtered_'));
    if(!isset($filter_fields[$filter_value])) {
        echo 'Invalid filter field.'; exit(0);
    }
}
else if(strpos($view_value, 'category_') === 0) {
    $category_value = substr($view_value, strlen('category_'));
    if(!isset($libs->categories[$category_value])) {
        echo 'Invalid category: '.htmlentities($category_value); exit(0);
    }
}
else {
    $filter_value = '';
    if(!isset($view_fields[$view_value])) {
        echo 'Invalid view value.'; exit(0);
    }
}

// Sort

$sort_value = isset($_GET['sort']) && $_GET['sort'] ?
    $_GET['sort'] : 'name';

if(!isset($sort_fields[$sort_value])) {
    echo 'Invalid sort field.'; exit(0);
}

// Page title

$page_title = boost_title().' Library Documentation';
if($category_value) $page_title.= ' - '. $libs->categories[$category_value]['title'];

// Functions

function library_filter($lib) {
  global $filter_value, $category_value;

  $libversion = explode('.',$lib['boost-version']);

  return boost_version($libversion[0],$libversion[1],$libversion[2]) &&
      (!$filter_value || ($lib[$filter_value] && $lib[$filter_value] !== 'false')) &&
      (!isset($_GET['filter']) || $lib[$_GET['filter']]) &&
      (!$category_value || $category_value === 'all' ||
        array_search($category_value, $lib['category']) !== FALSE);
}

// Library display functions:

function libref($lib)
{
  if (isset($lib['documentation']) && $lib['documentation'] != '')
  {
    if (isset($_SERVER["PATH_INFO"]) && $_SERVER["PATH_INFO"] != '' && $_SERVER["PATH_INFO"] != '/')
    {
      $docref = '/doc/libs'.$_SERVER["PATH_INFO"].'/'.$lib['documentation'];
    }
    else
    {
      $docref = '/doc/libs/1_41_0/'.$lib['documentation'];
    }
    print '<a href="'.$docref.'">'.($lib['name'] ? $lib['name'] : $lib['key']).'</a>';
  }
  else
  {
    print ($lib['name'] ? $lib['name'] : $lib['key']);
  }
}
function libauthors($lib)
{
  print ($lib['authors'] ? $lib['authors'] : '&nbsp;');
}
function libavailable($lib)
{
  print ($lib['boost-version'] ? $lib['boost-version'] : '&nbsp;');
}
function libstandard($lib)
{
  $p = array();
  if ($lib['std-proposal']) { $p[] = 'Proposed'; }
  if ($lib['std-tr1']) { $p[] = 'TR1'; }
  print ($p ? implode(', ',$p) : '&nbsp;');
}
function libbuildlink($lib)
{
  $p = array();
  if ($lib['header-only']) { $p[] = 'Header only'; }
  if ($lib['autolink']) { $p[] = 'Automatic linking'; }
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
    echo '<span>';
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

    echo '<a href="'.htmlentities($base_uri.$url_params).'">';
  }

  echo htmlentities($description);

  if($current_value == $value) {
    echo '</span>';
  }
  else {
    echo '</a>';
  }
}

function category_link($name, $category) {
  option_link(
    isset($category['title']) ? $category['title'] : $name,
    'view', 'category_'.$name);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title><?php echo htmlentities($page_title); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href="/style/section-doc.css" />
  <!--[if IE 7]> <style type="text/css"> body { behavior: url(/style/csshover3.htc); } </style> <![endif]-->
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
              <h1><?php echo htmlentities($page_title); ?></h1>
            </div>

            <div class="section-body">
              <div id="options">
                  <div id="view-options">
                    <ul class="menu">
                    <?php foreach($view_fields as $key => $description) : ?>
                      <li><?php option_link($description, 'view', $key); ?></li><?php
                    endforeach; ?>
                    <?php foreach($filter_fields as $key => $description) : ?>
                      <li><?php option_link($description, 'view', 'filtered_'.$key); ?></li>
                    <?php endforeach; ?>
                    </ul>
                  </div>
                  <div id="sort-options">
                    <div class="label">Sort by:</div>
                    <ul class="menu">
                    <?php foreach($display_sort_fields as $key => $description) : ?>
                      <li><?php option_link($description, 'sort', $key); ?></li>
                    <?php endforeach; ?>
                    </ul>
                  </div>
              </div>

              <?php if($view_value != 'categorized') { ?>

              <?php if($category_value) echo '<h2>', htmlentities($libs->categories[$category_value]['title']), '</h2>'; ?>

              <dl>
                <?php
                foreach ($libs->get($sort_value, 'library_filter') as $key => $lib) { ?>

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

                    <dt>Build&nbsp;&amp;&nbsp;Link</dt>

                    <dd><?php libbuildlink($lib); ?></dd>

                    <dt>Categories</dt>

                    <dd><?php libcategories($lib, $libs->categories); ?></dd>
                  </dl>
                </dd><!-- --><?php } ?>
              </dl>

              <?php } else { ?>

              <h2>By Category</h2>
              <?php
              foreach ($libs->get_categorized($sort_value, 'library_filter') as $name => $category) {
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
