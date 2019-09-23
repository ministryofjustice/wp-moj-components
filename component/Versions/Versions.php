<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-09-23
 * Time: 11:06
 */

namespace component;

/**
 * Returns version data specific to WordPress.
 */
class Versions
{
    public  function __construct()
    {
        $this->add_actions();
    }

    private function add_actions()
    {
        add_action('rest_api_init', [$this, 'register_api_endpoint']);
    }

    public function register_api_endpoint()
    {
        register_rest_route('moj', '/version', array(
            'methods' => 'GET',
            'callback' => [$this, 'wp_version']
        ));
    }

    public function wp_version()
    {
        global $wp_version;
        return $wp_version;
    }
}
