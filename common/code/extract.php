<?php
/*
  Copyright 2005 Redshift Software, Inc.
  Distributed under the Boost Software License, Version 1.0.
  (See accompanying file LICENSE_1_0.txt or http://www.boost.org/LICENSE_1_0.txt)
*/

if ($_SERVER['HTTP_HOST'] === 'boost.sourceforge.net') {
}
else if ($_SERVER['HTTP_HOST'] === 'boost.borg.redshift-software.com:8080') {
    @define('ARCHIVE_PREFIX', 'C:/DevRoots/Boost/boost_');
    @define('UNZIP', 'unzip');
}
else if ($_SERVER['HTTP_HOST'] === 'boost.redshift-software.com') {
    @define('ARCHIVE_PREFIX', '/export/website/boost/archives/boost_');
    @define('UNZIP', '/usr/local/bin/unzip');
}
@define('ARCHIVE_FILE_PREFIX', 'boost_');

function archive_file_path($pattern, $vpath)
{
    $path_parts = array();
    preg_match($pattern, $vpath, $path_parts);
    
    $key = $path_parts[2];
    $file = ARCHIVE_FILE_PREFIX . $path_parts[1] . '/' . $path_parts[2];
    $archive = str_replace('\\','/', ARCHIVE_PREFIX . $path_parts[1] . '.zip');
    
    return array($key,$file,$archive);
}

function archive_file_extract($path_parts)
{
    $key = $path_parts[0];
    $file = $path_parts[1];
    $archive = $path_parts[2];
    
    $type = null;
    if (preg_match('/^doc\/html\/.*html$/',$key)) { $type = 'boost.book.html'; }
    else if (preg_match('/^.*png$/',$key)) { $type = 'raw'; }
    else { return null; }
    
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
