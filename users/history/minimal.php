<?php
require_once(dirname(__FILE__) . '/../../common/code/boost_feed.php');
$_history = new boost_feed(dirname(__FILE__) . '/../../feed/history.rss', '/users/history');
$_guid = basename($_SERVER["PATH_INFO"]);
if(!$_guid) {
	$keys = array_keys($_history->db);
	$_guid = $keys[0];
}
if(!isset($_history->db[$_guid])) {
    header('HTTP/1.0 404 Not Found');
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
<?php if(!isset($_history->db[$_guid])) : ?>
  <h2>404 Not Found</h2>

  <p>The entry you requested could not be found.</p>
<?php else : ?>
  <h2><?php print $_history->db[$_guid]['title']; ?></h2>

  <?php print $_history->db[$_guid]['description']; ?>
<?php endif ?>
</body>
</html>
