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

        $gtm_id = $options['gtm_analytics_id'] ?? '';

        ?>
        <input type='text' name='moj_component_settings[gtm_analytics_id]'
               value='<?php echo $gtm_id; ?>' class="moj-component-input">
        <?php
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
