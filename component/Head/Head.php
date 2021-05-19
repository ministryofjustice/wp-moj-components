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
    public $addHeadElement;

    public function __construct()
    {
        $this->settings = new Settings();

        $this->actions();

        $options = get_option('moj_component_settings');
        $this->addHeadElement = $options['head_element'] ?? '';
    }

    public function actions()
    {
        add_action('wp_loaded', [$this->settings, 'settings'], 1);
        add_action('wp_head', [$this,'loadHeadElement']);
    }

    /**
     * Add meta
     *
     */
    public function loadHeadElement()
    {
        if (!empty($this->addHeadElement)) {
            echo $this->addHeadElement;
        }
    }
}
