<?php
require_once(dirname(__FILE__) . '/../common/code/boost_feed.php');
$_news = new boost_feed(
  'gmane.comp.lib.boost.user.rss',
  '/feed/gmane.comp.lib.boost.user.rss');
$_news->sort_by('pubdate');
?>
  <h4><a href="http://blog.gmane.org/gmane.comp.lib.boost.user">Recent
  User Topics <span class="link">&gt;</span></a></h4>

  <ul>
    <?php $_count = 0; foreach ( $_news->db as $_guid => $_item ) { $_count += 1; if ($_count > 5) { break; } ?>

    <li>
    <?php print '<a href="'.$_item['link'].'">'; ?><?php print $_item['title']; ?>
    <span class="link">&gt;</span><?php print '</a>'; ?><span class=
    "news-date"><?php print $_item['date']; ?></span></li><?php } ?>
  </ul>
