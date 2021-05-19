<?php

namespace MOJComponents\Head;

class HeadSettings extends Head
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
            'head_element',
            __('Element', 'wp-moj-components'),
            [$this, 'addHeadElement'],
            'mojComponentSettings',
            $section
        );
    }

    /**
     * Function that collects inputed GTM ID and running checks on it.
     */
    public function addHeadElement()
    {
        $options = get_option('moj_component_settings');
        $headElement = $options['head_element'] ?? '';

        ?>
        <input type='text' name='moj_component_settings[head_element]'
        placeholder="For example, add <meta> element" value='<?php echo $headElement; ?>'
        class="moj-component-input">
        <?php
    }

    public function settingsSectionCB()
    {
        ?>
        <div class="welcome-panel-column">
            <h4><?php _e('Add HTML elements to wp_head', 'wp-moj-components') ?></h4>
            <p style="max-width: 600px"><?php _e('Add HTML meta-related elements to the head.
            On WP multisite, this applies to the specific site your logged in as.
            This is normally for validating an external app, testing a CDN script,
            or SEO tagging that cannot be done via Yoast.', 'wp-moj-components'); ?></p>
        </div>
        <?php
    }
}
