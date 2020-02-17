<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-09-23
 * Time: 11:06
 */

namespace component;

use component\Versions\Plugins as Plugins;

/**
 * Returns version data specific to WordPress.
 */
class Versions
{
    public $plugins;

    public function __construct()
    {
        $this->actions();
        $this->plugins();
    }

    private function actions()
    {
        add_action('rest_api_init', [$this, 'registerWPVersionApiEndpoint']);
        add_action('rest_api_init', [$this, 'registerPluginApiEndpoint']);
    }

    public function plugins()
    {
        $this->plugins = new Plugins();
        define('MOJ_COMPONENT_VERSION', $this->plugins->data());
    }

    public function registerWPVersionApiEndpoint()
    {
        return register_rest_route('moj', '/version', array(
            'methods' => 'GET',
            'callback' => [$this, 'wpVersion']
        ));
    }

    public function registerPluginApiEndpoint()
    {
        return register_rest_route('moj', '/plugin-versions', array(
            'methods' => 'GET',
            'callback' => [$this, 'pluginVersions']
        ));
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function wpVersion()
    {
        global $wp_version;
        return $wp_version;
    }

    public function pluginVersions()
    {
        return $this->plugins->get();
    }
}
