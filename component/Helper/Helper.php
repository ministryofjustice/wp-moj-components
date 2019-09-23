<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-09-23
 * Time: 13:56
 */

namespace component;

/**
 * Contains useful methods
 */
class Helper
{
    public $asset_path = '';

    public  function __construct()
    {
        $this->add_actions();
    }

    private function add_actions()
    {
    }

    public function get_page_uri()
    {
        global $wp;
        return home_url($wp->request);
    }

    /**
     * @param $path | the path to the assets directory in the given component
     * @return string
     */
    public function asset_path($path)
    {
        return esc_url(plugins_url('/assets/', $path));
    }

    public function css_path($path)
    {
        return $this->asset_path($path) . '/css/';
    }

    public function image_path($path)
    {
        return $this->asset_path($path) . '/images/';
    }
}

function Helper()
{
    return new Helper();
}

/**
 * backward compat helper function
 */
function moj_get_page_uri()
{
    return Helper()->get_page_uri();
}

