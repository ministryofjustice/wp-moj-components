<?php

namespace component;

use component\Security\VulnerabilityDB as VulnerabilityDB;

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
        add_filter('sanitize_file_name',  [$this, 'remove_filename_bad_chars'], 10);
    }

    public static function vulndb()
    {
        return new VulnerabilityDB();
    }

    public static function remove_filename_bad_chars($filename) {

        $bad_chars = array( '–', '#', '~', '%', '|', '^', '>', '<', '['. ']', '{', '}');
        $filename = str_replace($bad_chars, "-", $filename);
        return $filename;

    }

}
