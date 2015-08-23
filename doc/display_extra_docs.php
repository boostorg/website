<?php

if (strpos($_SERVER['REQUEST_URI'], '//') !== FALSE)
{
	header("Location: http://$_SERVER[HTTP_HOST]".
		preg_replace('@//+@','/', $_SERVER['REQUEST_URI']),
		TRUE, 301);
	exit(0);
}

require_once(dirname(__FILE__) . '/../common/code/boost.php');

$archive = new BoostArchive(array(
    'zipfile' => false,
));

$archive->display_from_archive(
  array(
  array('', '@[.](html|htm)$@i','basic','text/html')
  )
);
