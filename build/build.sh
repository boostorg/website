#!/bin/sh

php build.php
php index-src.php > ../index.html
php news-index.php > ../users/news/index.html
php news-entries.php
php download-index.php > ../users/download/index.html
php download-entries.php
php history-index.php > ../users/history/index.html
php history-entries.php

