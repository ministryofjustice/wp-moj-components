<?php

namespace component\Sitemap;

use component\Sitemap as Sitemap;

class SitemapSettings extends Sitemap
{
    public $helper;

    public function __construct()
    {
        global $mojHelper;
        $this->helper = $mojHelper;

        //parent::__construct();
    }

    public function settings()
    {
        $this->helper->initSettings($this);
    }

    public function settingsFields($section)
    {
        add_settings_field(
            'sitemap_exclude_pages',
            __('Exclude pages', 'wp-moj-components'),
            [$this, 'popupMessageTitleCB1'],
            'mojComponentSettings',
            $section
        );

        add_settings_field(
            'sitemap_exclude_cpt_page',
            __('Exclude CPT - Page', 'wp-moj-components'),
            [$this, 'sitemapOptionCheckbox'],
            'mojComponentSettings',
            $section,
            array('option_name' => 'sitemap_exclude_cpt_page')
        );

        add_settings_field(
            'sitemap_exclude_cpt_post',
            __('Exclude CPT - Post', 'wp-moj-components'),
            [$this, 'sitemapOptionCheckbox'],
            'mojComponentSettings',
            $section,
            array('option_name' => 'sitemap_exclude_cpt_post')
        );

        add_settings_field(
            'sitemap_exclude_cpt_archive',
            __('Exclude CPT - Archive', 'wp-moj-components'),
            [$this, 'sitemapOptionCheckbox'],
            'mojComponentSettings',
            $section,
            array('option_name' => 'sitemap_exclude_cpt_archive')
        );

        add_settings_field(
            'sitemap_exclude_cpt_author',
            __('Exclude CPT - Author', 'wp-moj-components'),
            [$this, 'sitemapOptionCheckbox'],
            'mojComponentSettings',
            $section,
            array('option_name' => 'sitemap_exclude_cpt_author')
        );

        $args = array(
            'public' => true,
            '_builtin' => false
        );

        $post_types = get_post_types($args, 'names');

        // list all the CPT
        foreach ($post_types as $post_type) {

            // extract CPT object
            $cpt = get_post_type_object($post_type);

            add_settings_field(
                'sitemap_exclude_cpt_' . $cpt->name,
                __('Exclude CPT - ' . $cpt->label, 'wp-moj-components'),
                [$this, 'sitemapOptionCheckbox'],
                'mojComponentSettings',
                $section,
                array('option_name' => 'sitemap_exclude_cpt_' . $cpt->name)
            );
        }

        // Get the Taxonomies
        $args = array(
            'public' => true,
            '_builtin' => false
        );
        $taxonomies_names = get_taxonomies($args);

        // list all the taxonomies
        foreach ($taxonomies_names as $taxonomy_name) {

            // Extract
            $taxonomy_obj = get_taxonomy($taxonomy_name);

            // get some data
            $taxonomy_name = $taxonomy_obj->name;
            $taxonomy_label = $taxonomy_obj->label;

            add_settings_field(
                'sitemap_exclude_taxonomy_' . $taxonomy_name,
                __('Exclude Tax - ' . $taxonomy_label, 'wp-moj-components'),
                [$this, 'sitemapOptionCheckbox'],
                'mojComponentSettings',
                $section,
                array('option_name' => 'sitemap_exclude_taxonomy_' . $taxonomy_name)
            );
        }

        add_settings_field(
            'sitemap_exclude_password_protected',
            __('Exclude Password Protected', 'wp-moj-components'),
            [$this, 'sitemapOptionCheckbox'],
            'mojComponentSettings',
            $section,
            array('option_name' => 'sitemap_exclude_password_protected')
        );
    }


    public function sitemapOptionCheckbox($args)
    {
        $options = get_option('moj_component_settings');
        ?>
        <input type='checkbox' name='moj_component_settings[<?php echo $args['option_name']; ?>]'
               value='yes' <?= checked('yes', $options[$args['option_name']] ?? '') ?>
               class="moj-component-input-checkbox">
        <?php

        return null;
    }

    public function popupMessageTitleCB1()
    {
        $options = get_option('moj_component_settings');

        $excludePages = $options['sitemap_exclude_pages'] ?? '';

        ?>
        <input type='text' name='moj_component_settings[sitemap_exclude_pages]'
               value='<?php echo $excludePages; ?>' class="moj-component-input">
        <?php
    }

    public function settingsSectionCB()
    {
        ?>
        <div class="welcome-panel-column">
            <h4><?php _e('Traditionnal sitemap', 'wp_sitemap_page') ?></h4>
            <p><?php _e('To display a traditional sitemap, just use [wp_sitemap_page] on any page or post.', 'wp_sitemap_page'); ?></p>
        </div>

        <div class="welcome-panel-column">
            <h4><?php _e('Display only some content', 'wp_sitemap_page') ?></h4>
            <p><?php _e('Display only some kind of content using the following shortcodes.', 'wp_sitemap_page'); ?></p>
            <ul>
                <li>[wp_sitemap_page only="post"]</li>
                <li>[wp_sitemap_page only="page"]</li>
                <li>[wp_sitemap_page only="category"]</li>
                <li>[wp_sitemap_page only="tag"]</li>
                <li>[wp_sitemap_page only="archive"]</li>
                <li>[wp_sitemap_page only="author"]</li>
            </ul>
        </div>
        <?php
    }
}
