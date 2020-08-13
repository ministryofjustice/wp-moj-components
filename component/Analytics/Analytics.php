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

    /**
     * @var string
     */
    public $googleTagManagerID;

    public function __construct()
    {
        $this->settings = new Settings();

        $this->actions();

        // Get GTM ID if provided via the settings field
        $options = get_option('moj_component_settings');
        $this->googleTagManagerID = $options['gtm_analytics_id'] ?? '';
    }

    public function actions()
    {
        add_action('wp_loaded', [$this->settings, 'settings'], 1);
        add_action('wp_head',[$this,'loadGTMCodeHead']);
        add_action('wp_body_open',[$this,'loadGTMCodeBody']);
    }

    /**
     * Add code as per Google guidance.
     *
     * https://developers.google.com/tag-manager/quickstart
     */
    public function loadGTMCodeHead() {

        // We only want to display GTM code when an ID has been entered.
        if (!empty($this->googleTagManagerID)) {
            ?>
                <!-- Google Tag Manager -->
                <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                })(window,document,'script','dataLayer', '<?php echo sanitize_html_class($this->googleTagManagerID); ?>' );</script>
                <!-- End Google Tag Manager -->
            <?php
        }
    }

    public function loadGTMCodeBody() {
        if (!empty($this->googleTagManagerID)) {
            ?>
                <!-- Google Tag Manager (noscript) -->
                <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo sanitize_html_class($this->googleTagManagerID); ?>"
                height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
                <!-- End Google Tag Manager (noscript) -->
            <?php
        }
    }
}