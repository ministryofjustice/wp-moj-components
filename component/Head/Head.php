<?php

namespace MOJComponents\Head;

use MOJComponents\Head\HeadSettings as Settings;

class Head
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
    public $addHeadTag;

    public function __construct()
    {
        $this->settings = new Settings();

        $this->actions();

        $options = get_option('moj_component_settings');
        $this->addHeadTag = $options['head_tag'] ?? '';
    }

    public function actions()
    {
        add_action('wp_loaded', [$this->settings, 'settings'], 1);
        add_action('wp_head', [$this,'loadHeadTag']);
    }

    /**
     * Add meta
     *
     */
    public function loadHeadTag()
    {
        if (!empty($this->addHeadTag)) {
            echo $this->addHeadTag;
        }
    }
}
