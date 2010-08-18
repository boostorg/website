#!/bin/sh

php build.php
php index-src.php > ../index.html
php download-entries.php
php news-entries.php
php history-entries.php