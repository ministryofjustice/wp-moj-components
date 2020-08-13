<?php

namespace component\Analytics;

use component\Analytics as Analytics;

class AnalyticsSettings extends Analytics
{
    public $helper;

    public function __construct()
    {
        global $mojHelper;
        $this->helper = $mojHelper;
    }

    public function settings()
    {
        $this->helper->initSettings($this);
    }

    public function settingsFields($section)
    {
        add_settings_field(
            'gtm_analytics_id',
            __('GTM ID', 'wp-moj-components'),
            [$this, 'gtmAnalyticsId'],
            'mojComponentSettings',
            $section
        );
    }

    public function gtmAnalyticsId()
    {
        $options = get_option('moj_component_settings');
        $googleTagManagerID = $options['gtm_analytics_id'] ?? '';

        ?>
        <input type='text' name='moj_component_settings[gtm_analytics_id]'
        placeholder="GTM-XXXXXXX" value='<?php echo sanitize_html_class($googleTagManagerID); ?>' 
        class="moj-component-input">
        <?php

        // Run a few basic checks (mainly for devs in case of C&P typos)

        // Check if empty string stop rest of checks.
        if ($googleTagManagerID === '') {
            return;
        }

        // Remove whitespace, tabs & line ends.
        $googleTagManagerID = preg_replace('/\s+/', '', $googleTagManagerID);

        // Too many, too few characters
        if (strlen($googleTagManagerID) != 11) {
            echo '<div class="notice notice-error settings-error" style="margin-left: 0;">
            GTM ID might be invalid. Double check the charactor count.</div>';
        }

        // Check it is a GTM ID (not GA for example)
        if (!preg_match('/^GTM-/', $googleTagManagerID)) {
            echo '<div class="notice notice-error settings-error" style="margin-left: 0;">
            GTM ID might be invalid. ID must start with GTM.</div>';
        }
    }

    public function settingsSectionCB()
    {
        ?>
        <div class="welcome-panel-column">
            <h4><?php _e('Google Tag Manager', 'wp_analytics_page') ?></h4>
            <p><?php _e('Add GTM code below', 'wp_analytics_page'); ?></p>
        </div>
        <?php
    }
}
