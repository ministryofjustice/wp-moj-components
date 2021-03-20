<?php

namespace MOJComponents\Security;

class Security
{
    public $helper;

    public function __construct()
    {
        global $mojHelper;
        $this->helper = $mojHelper;

        $this->hooks();

        // init vulnerability check
        new VulnerabilityDB();
    }

    public function hooks()
    {
        add_filter('sanitize_file_name',  [$this, 'removeFilenameBadChars'], 10);
    }

    public static function removeFilenameBadChars($filename) {

        $bad_chars = array( '–', '#', '~', '%', '|', '^', '>', '<', '['. ']', '{', '}');
        $filename = str_replace($bad_chars, "-", $filename);
        return $filename;

    }
}
