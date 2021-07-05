<?php

namespace MOJComponents\Versions;

class Plugins
{
    /**
     * @var string
     */
    public $parentPath = '';

    public function __construct()
    {
        $this->actions();
    }

    public function actions()
    {
        add_action('after_setup_theme', [$this, 'load']);
    }

    /**
     * Loads system plugins and filters any incompatible results
     * @return array|bool on reload returns an array with newly set plugins, false otherwise
     */
    public function load()
    {
        if (false === get_transient('moj_plugin_versions_check')) {
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $plugins = get_plugins();
            set_transient('moj_plugin_versions_check', true, DAY_IN_SECONDS);

            return $this->set($plugins);
        }

        return false;
    }

    public function set(array $plugins)
    {
        $structure = [];
        foreach ($plugins as $plugin_key => $plugin ) {

            strpos($plugin_key, '/');

            if(!empty(strpos($plugin_key, '/'))){

                $plugin_slug = substr($plugin_key, 0, strpos($plugin_key, '/'));
                $structure[$plugin_slug] = $plugin['Version'];

            }
        }

        update_option('moj_plugin_versions', $structure);
        return $structure;
    }

    public function get()
    {
        return get_option('moj_plugin_versions');
    }

    /**
     * Get the version of a plugin.
     * If a plugin file path is not passed, the version will default to this plugin; MoJ Components
     * @param $plugin_file_path string absolute path to the plugin file needed to extract data.
     * @return mixed
     */
    public function data($plugin_file_path = '')
    {
        if ($plugin_file_path === '') {
            $plugin_file_path = MOJ_COMPONENT_PLUGIN_PATH;
        }

        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $plugin = get_plugin_data($plugin_file_path, false, false);
        return $plugin['Version'] ?? false;
    }
}
