<?php

namespace component\Versions;

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
        foreach ($plugins as $plugin) {
            if (!empty($plugin['Author'])) {
                $structure[$plugin['TextDomain']] = $plugin['Version'];
            }
        }

        update_option('moj_plugin_versions', $structure);
        return $structure;
    }

    public function get()
    {
        return get_option('moj_plugin_versions');
    }
}
