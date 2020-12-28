<?php error_reporting (E_ALL ^ E_NOTICE); ?>
<?php

require_once(dirname(__FILE__) . '/../common/code/bootstrap.php');

class LibraryPage {
    static $param_defaults = Array(
        'view' => 'all',  // all/categorized
                          // filtered_std-proposal, filtered_std-tr1
                          // category_*
        'sort' => 'name', // Field to sort libraries by.
        'filter' => null, // Filter by whether an attribute is present.
                          // Not used for a long time, could probably be
                          // dropped.
    );

    static $view_fields = Array(
        'all' => 'All',
        'categorized' => 'Categorized',
        'condensed' => 'Condensed',
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
        'name' => 'Name',
        'boost-version' => 'First Release'
    );

    var $params;            // Normalised URL parameters
    var $libs;
    var $documentation_page;
    var $categories;

    var $page_url_path;     // URL path for this page
    var $view_value;
    var $sort_value;
    var $attribute_filter;
    var $category_value;
    var $filter_value;

    function __construct($params, $libs) {
        $this->libs = $libs;
        $this->categories = $libs->get_categories();

        $this->documentation_page = BoostDocumentation::library_documentation_page();

        $page_url_path = $_SERVER['REQUEST_URI'];
        $page_url_path = preg_replace('@[#?].*@', '', $page_url_path);
        $page_url_path = preg_replace('@//+@', '/', $page_url_path);
        $this->page_url_path = $page_url_path;

        $this->params = array();
        foreach (self::$param_defaults as $key => $default) {
            // Note: Using default for empty values as well as missing values.
            $this->params[$key] = strtolower(trim(
                BoostWebsite::array_get($params, $key))) ?: $default;
        }

        $this->view_value = $this->params['view'];
        if (strpos($this->view_value, 'filtered_') === 0) {
            $this->filter_value = substr($this->view_value, strlen('filtered_')) ?: '';

            if (!array_key_exists($this->filter_value, self::$filter_fields)) {
                BoostWeb::throw_http_error(400, "Malformed request",
                    "Invalid filter field: {$this->filter_value}");
            }
            if (self::$filter_fields[$this->filter_value] == '[old]') {
                BoostWeb::throw_http_error(410, 'Filter field no longer supported.',
                    "Filter field {$this->filter_value} is no longer supported");
            }
            $this->view_value = 'all';
        }
        else if (strpos($this->view_value, 'category_') === 0) {
            $this->category_value = substr($this->view_value, strlen('category_')) ?: '';
            if(!array_key_exists($this->category_value, $this->categories)) {
                BoostWeb::throw_http_error(400, "Invalid category",
                    "Invalid category: {$this->category_value}");
            }
            $this->view_value = 'all';
        }
        else {
            if (!array_key_exists($this->view_value, self::$view_fields)) {
                BoostWeb::throw_http_error(400, 'Invalid view value',
                    "Invalid view value: {$this->view_value}");
            }
        }

        $this->sort_value = $this->params['sort'];
        if (!array_key_exists($this->sort_value, self::$sort_fields)) {
            BoostWeb::throw_http_error(400, 'Invalid sort field',
                "Invalid sort value: {$this->sort_value}");
        }

        $this->attribute_filter = $this->params['filter'];
        if ($this->attribute_filter) {
            if (!preg_match('@^[-_a-zA-Z0-9]+$@', $this->attribute_filter)) {
                BoostWeb::throw_http_error(400, 'Invalid attribute filter',
                    "Invalid attribute filter: {$this->attribute_filter}");
            }
        }

        if ($this->documentation_page->version->is_numbered_release() &&
                $this->libs->latest_version &&
                $this->documentation_page->version->compare($this->libs->latest_version) > 0)
        {
            BoostWeb::throw_error_404($_SERVER['REQUEST_URI']);
        }
    }

    function filter($lib) {
        if (!BoostLibraries::filter_visible($lib)) {
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
        $page_title = "Boost";
        if ($this->documentation_page->version_title) {
            $page_title .= " {$this->documentation_page->version_title}";
        }
        $page_title .= " Library Documentation";
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
        return $this->libs->get_for_version(
                $this->documentation_page->version,
                $this->sort_value,
                array($this, 'filter'));
    }

    function categorized_libraries() {
        return $this->libs->get_categorized_for_version(
                $this->documentation_page->version,
                $this->sort_value,
                array($this, 'filter'));
    }

    // Library display functions:

    function libid($lib) {
        $id = trim(preg_replace('@[^a-zA-Z0-9]+@', '-', $lib['key']), '-');
        echo "lib-{$id}";
    }

    function libref($lib) {
        if (!empty($lib['documentation'])) {
            $docref = "/doc/libs/{$this->documentation_page->url_doc_dir}/{$lib['documentation']}";
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
        print $lib['boost-version']->is_update_version() ?
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

    function libstandard_min_level($lib) {
        print !empty($lib['cxxstd']) ?
                html_encode($lib['cxxstd']) : '&nbsp;';
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

    function category_link($name) {
        $category = $this->categories[$name];
        $this->option_link(
                isset($category['title']) ? $category['title'] : $name,
                'view', 'category_' . $name);
    }
}

$library_page = new LibraryPage($_GET, BoostLibraries::load());

if ($library_page->documentation_page->redirect_if_appropriate()) {
    return;
}

// To avoid confusion, only show this page when there is actual documentation.
if (!is_dir($library_page->documentation_page->documentation_dir())) {
    BoostWeb::throw_error_404($_SERVER['REQUEST_URI']);
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
  <?php echo latest_link($library_page->documentation_page); ?>

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

              <?php if ($library_page->view_value == 'all'): ?>

              <?php $library_page->category_subtitle(); ?>
              <dl>
              <?php foreach ($library_page->filtered_libraries() as $lib): ?>
                <dt id="<?php echo $library_page->libid($lib); ?>"><?php $library_page->libref($lib); ?></dt>
                <dd>
                  <p><?php $library_page->libdescription($lib); ?></p>
                  <dl class="fields">
                    <dt>Author(s)</dt>
                    <dd><?php $library_page->libauthors($lib); ?></dd>
                    <dt>First&nbsp;Release</dt>
                    <dd><?php $library_page->libavailable($lib); ?></dd>
                    <dt>Standard</dt>
                    <dd><?php $library_page->libstandard($lib); ?></dd>
                    <?php if (isset($lib['cxxstd'])): ?>
                    <dt>C++ standard minimum level</dt>
                    <dd><?php $library_page->libstandard_min_level($lib); ?></dd>
                    <?php endif ?>
                    <dt>Categories</dt>
                    <dd><?php $library_page->libcategories($lib); ?></dd>
                  </dl>
                </dd>
              <?php endforeach; ?>
              </dl>

              <?php elseif ($library_page->view_value == 'condensed'): ?>

              <?php $library_page->category_subtitle(); ?>
              <ul>
              <?php foreach ($library_page->filtered_libraries() as $lib): ?>
                <li id="<?php echo $library_page->libid($lib); ?>">
                <?php
                  $library_page->libref($lib);
                  echo ': ';
                  $library_page->libdescription($lib);
                ?>
                </li>
              <?php endforeach; ?>
              </ul>

              <?php else: ?>

              <h2>By Category</h2>
              <?php
              foreach ($library_page->categorized_libraries() as $name => $category) {
                if(count($category['libraries'])) {
                  echo '<h3>';
                  echo "\n";
                  $library_page->category_link($name);
                  echo '</h3>';
                  echo '<ul>';
                  foreach ($category['libraries'] as $lib) {
                    echo '<li>';
                    $library_page->libref($lib);
                    echo ': ';
                    $library_page->libdescription($lib);
                    echo '</li>';
                    echo "\n";
                  }
                  echo '</ul>';
                  echo "\n";
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
