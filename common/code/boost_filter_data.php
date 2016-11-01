<?php

class BoostFilterData {
    var $version;
    var $path;
    var $content;
    var $noindex = false;
    var $boost_root = '';
    // Needed for latest_link, I'd like to remove this, perhaps by
    // link details earlier, and storing the result instead.
    var $archive_dir = null;
    var $fix_dir = null;
}
