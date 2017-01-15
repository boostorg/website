<?php

if (strpos($_SERVER['REQUEST_URI'], '//') !== FALSE)
{
	header("Location: http://$_SERVER[HTTP_HOST]".
		preg_replace('@//+@','/', $_SERVER['REQUEST_URI']),
		TRUE, 301);
	exit(0);
}

require_once(dirname(__FILE__) . '/../common/code/bootstrap.php');

BoostDocumentation::extra_documentation_page()->display_from_archive(
  array(
  array('', '@[.](html|htm)$@i','basic','text/html')
  )
);
