<?php

if ($_SERVER['HTTP_HOST'] === 'boost.sourceforge.net') {
}
else if ($_SERVER['HTTP_HOST'] === 'boost.borg.redshift-software.com:8080') {
	@define('ARCHIVE_PREFIX', 'C:/DevRoots/Boost/boost_');
	@define('UNZIP', 'unzip');
}
else if ($_SERVER['HTTP_HOST'] === 'boost.redshift-software.com') {
	@define('ARCHIVE_PREFIX', '/export/website/boost/archives/boost_');
	@define('UNZIP', 'unzip');
}
@define('ARCHIVE_FILE_PREFIX', 'boost_');

function print_archive_file($pattern, $vpath)
{
	$path_parts = array();
	preg_match($pattern, $vpath, $path_parts);
	
	$type = null;
	if (preg_match('/^doc\/html\/.*html$/',$path_parts[2])) { $type = 'boost.book.html'; }
	else if (preg_match('/^.*png$/',$path_parts[2])) { $type = 'raw'; }
	else { return null; }
	
	$archive = str_replace('\\','/', ARCHIVE_PREFIX . $path_parts[1] . '.zip');
	$file = ARCHIVE_FILE_PREFIX . $path_parts[1] . '/' . $path_parts[2];
	$unzip = UNZIP . ' -p "' . $archive . '" "' . $file . '"';
	$f_handle = popen($unzip,'rb');
	if ($type === 'raw') {
		fpassthru($f_handle);
	}
	else {
		$text = '';
		while ($f_handle && !feof($f_handle)) {
			$text .= fread($f_handle,8*1024);
		}
	}
	pclose($f_handle);
	
	if ($type === 'boost.book.html') {
		$text = substr($text,strpos($text,'<div class="spirit-nav">'));
		$text = substr($text,0,strpos($text,'</body>'));
		for ($i = 0; $i < 8; $i++) {
			$text = preg_replace(
				'@<img src="[\./]*images/(.*\.png)" alt="(.*)"([ ][/])?>@Ssm',
				'<img src="/style/css_0/${1}" alt="${2}" />',
				$text );
		}
		$text = str_replace('<hr>','',$text);
		$text = str_replace('<table width="100%">','<table class="footer-table">',$text);
		
		#print htmlentities($text);
		print $text;
	}
}

?>
