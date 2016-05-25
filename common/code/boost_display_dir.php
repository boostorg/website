<?php

// Not strictly a filter, but needs some of the members.
class BoostDisplayDir extends BoostFilter
{
    function display($dir) {
        $handle = opendir($dir);

        $title = html_encode("Index listing for {$this->data->path}");

        $this->title = $title;
        $this->data->noindex = true;

        $content = "<h3>$title</h3>\n<ul>\n";

        while (($file = readdir($handle)) !== false)
        {
            if (substr($file, 0, 1) == '.') continue;
            if (is_dir("$dir$file")) $file .= '/';
            $file = html_encode($file);
            $content .= "<li><a rel='nofollow' href='$file'>$file</a></li>\n";
        }

        $content .= "</ul>\n";

        $this->data->content = $content;

        $this->display_template($this->template_params($content));
    }
}
