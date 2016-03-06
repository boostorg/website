<?php

class BoostFilterData {
    var $version;
    var $path;
    var $content;
    var $noindex = false;
    // Needed for latest_link, I'd like to remove this, perhaps by
    // link details earlier, and storing the result instead.
    var $archive_dir = null;

    function __construct($params) {
        $this->version = array_key_exists('version', $params) ?
            $params['version'] : null;
        $this->path = array_key_exists('key', $params) ?
            $params['key'] : null;
        $this->content = array_key_exists('content', $params) ?
            $params['content'] : null;
        $this->archive_dir = array_key_exists('archive_dir', $params) ?
            $params['archive_dir'] : null;
    }
}
