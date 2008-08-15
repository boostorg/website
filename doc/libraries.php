<?php
require_once(dirname(__FILE__) . '/../common/code/boost_version.php');
require_once(dirname(__FILE__) . '/../common/code/boost_libraries.php');

$libs = new boost_libraries(dirname(__FILE__) . '/libraries.xml');
if (isset($_REQUEST['sort']))
{
  $libs->sort_by($_REQUEST['sort']);
}
else
{
  $libs->sort_by('name');
}

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
      $docref = '/doc/libs/1_36_0/'.$lib['documentation'];
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
  if ($lib['std-proposal'] == 'true') { $p[] = 'Proposed'; }
  if ($lib['std-tr1'] == 'true') { $p[] = 'TR1'; }
  print ($p ? implode(', ',$p) : '&nbsp;');
}
function libbuildlink($lib)
{
  $p = array();
  if ($lib['header-only'] == 'true') { $p[] = 'Header only'; }
  if ($lib['autolink'] == 'true') { $p[] = 'Automatic linking'; }
  print ($p ? implode(', ',$p) : '&nbsp;');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title>Boost C++ Libraries</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="icon" href="/favicon.ico" type="image/ico" />
  <link rel="stylesheet" type="text/css" href="/style/section-doc.css" />
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
              <h1>Boost C++ Libraries</h1>
            </div>

            <div class="section-body">
              <ul class="menu">
                <li>Sort By</li>

                <li><a href="?sort=name">Name</a></li>

                <li><a href="?sort=boost-version">First Release</a></li>

                <li><a href="?sort=std-proposal">STD Proposal</a></li>

                <li><a href="?sort=std-tr1">STD::TR1</a></li>

                <li><a href="?sort=header-only">Header Only Use</a></li>

                <li><a href="?sort=autolink">Automatic Linking</a></li>

                <li><a href="?sort=key">Key</a></li>
              </ul>

              <dl>
                <?php
                foreach ($libs->db as $key => $lib) {
                  $libversion = explode('.',$lib['boost-version']);
                  if (boost_version($libversion[0],$libversion[1],$libversion[2])) { ?>

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
                  </dl>
                </dd><!-- --><?php } } ?>
              </dl>
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
