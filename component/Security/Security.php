<?php

namespace MOJComponents\Security;

use MOJComponents\Security\VulnerabilityDB as VulnerabilityDB;

class Security
{
    public $helper;

    public $vulndb = null;

    public function __construct()
    {
        global $mojHelper;
        $this->helper = $mojHelper;

        $this->hooks();
        $this->vulndb();
    }

    public function hooks()
    {
        add_filter('sanitize_file_name',  [$this, 'removeFilenameBadChars'], 10);
    }

    public static function vulndb()
    {
        return new VulnerabilityDB();
    }

    public static function removeFilenameBadChars($filename) {

        $bad_chars = array( '–', '#', '~', '%', '|', '^', '>', '<', '['. ']', '{', '}');
        $filename = str_replace($bad_chars, "-", $filename);
        return $filename;

    }

}
