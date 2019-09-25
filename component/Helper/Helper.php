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

    public function get_time_period()
    {
        if (date("H") < 12) {
            return "morning";

        } elseif (date("H") > 11 && date("H") < 17) {

            return "afternoon";

        } elseif (date("H") > 16) {

            return "evening";

        }
    }

    /**
     * @param $path | the path to the assets directory in the given component
     * @return string
     */
    public function asset_path($path)
    {
        return esc_url(plugins_url('assets/', $path));
    }

    public function css_path($path)
    {
        return $this->asset_path($path) . 'css/';
    }

    public function font_path($path)
    {
        return $this->asset_path($path) . 'fonts/';
    }

    public function image_path($path)
    {
        return $this->asset_path($path) . 'images/';
    }

    public function js_path($path)
    {
        return $this->asset_path($path) . 'js/';
    }
}

/**
 * backward compat helper function
 */
function moj_get_page_uri()
{
    $helper = new Helper();
    return $helper->get_page_uri();
}

