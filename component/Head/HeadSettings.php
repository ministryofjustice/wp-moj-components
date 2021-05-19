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
            'head_tag',
            __('Tag', 'wp-moj-components'),
            [$this, 'addHeadTag'],
            'mojComponentSettings',
            $section
        );
    }

    /**
     * Function that collects inputed GTM ID and running checks on it.
     */
    public function addHeadTag()
    {
        $options = get_option('moj_component_settings');
        $addHeadTag = $options['head_tag'] ?? '';

        ?>
        <input type='text' name='moj_component_settings[head_tag]'
        placeholder="For example, add <meta> tag" value='<?php echo $addHeadTag; ?>'
        class="moj-component-input">
        <?php
    }

    public function settingsSectionCB()
    {
        ?>
        <div class="welcome-panel-column">
            <h4><?php _e('Add HTML tags to wp_head', 'wp-moj-components') ?></h4>
            <p style="max-width: 600px"><?php _e('Add a HTML tag to the head.
            On WP multisite is only applies to a specific site.
            This is normally for validating an external app, testing a CDN script,
            or SEO tagging that cannot be done via Yoast.', 'wp-moj-components'); ?></p>
        </div>
        <?php
    }
}
