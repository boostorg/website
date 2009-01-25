<?php
require_once(dirname(__FILE__) . '/../../common/code/boost_feed.php');
$_history = new boost_feed(dirname(__FILE__) . '/../../feed/history.rss', '/users/history');
$_guid = basename($_SERVER["PATH_INFO"]);
if(!isset($_history->db[$_guid])) {
    require_once(dirname(__FILE__) . '/../../common/code/boost_error_page.php');
    error_page_404();
    exit(0);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title><?php print $_history->db[$_guid]['title']; ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="icon" href="/favicon.ico" type="image/ico" />
</head>

<body>
  <h2><?php print $_history->db[$_guid]['title']; ?></h2>

  <p><span class="news-date"><?php print $_history->db[$_guid]['date']; ?></span></p>

  <?php print $_history->db[$_guid]['description']; ?>
</body>
</html>
