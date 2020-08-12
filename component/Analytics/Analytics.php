<?php

namespace component;

use component\Analytics\AnalyticsSettings as Settings;

class Analytics
{
    /**
     * @var string
     */
    public $parentPath = '';


    /**
     * @var boolean
     */
    public $hasSettings = true;

    /**
     * @var object
     */
    public $settings;

    public function __construct()
    {

        $this->settings = new Settings();

        $this->actions();
    }

    public function actions()
    {
        add_action('wp_head',[$this,'loadAnalyticsCode']);
    }
    
    public function loadAnalyticsCode() {
        ?>
            <!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-testing');</script>
            <!-- End Google Tag Manager -->
        <?php
    }
}