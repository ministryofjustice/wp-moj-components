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

        $this->actions();
        $this->vulndb();
    }

    public function actions()
    {
        // add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue()
    {
        // wp_enqueue_style('security_admin_css', $this->helper->cssPath(__FILE__) . 'main.css', []);
    }

    public static function vulndb()
    {
        return new VulnerabilityDB();
    }
}
