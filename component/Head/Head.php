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
    public $headElement;

    public function __construct()
    {
        $this->settings = new Settings();

        $this->actions();

        $options = get_option('moj_component_settings');
        $this->headElement = $options['head_element'] ?? '';
    }

    public function actions()
    {
        add_action('wp_loaded', [$this->settings, 'settings'], 1);
        add_action('wp_head', [$this,'loadHeadElement']);
    }

    /**
     * Print into head inputed element/code
     *
     */
    public function loadHeadElement()
    {
        if (!empty($this->headElement)) {
            echo $this->headElement;
        }
    }
}
