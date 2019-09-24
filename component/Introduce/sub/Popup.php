<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-09-23
 * Time: 15:31
 */

namespace component;


class Popup
{
    public $parent_path = '';

    public function __construct()
    {
    }

    public function init()
    {
        $this->add_action();
    }

    public function add_action()
    {
        add_action('wp_ajax_popup_dismissed', array(__CLASS__, 'timer'));

        //$this->timer_clear();

        if (!get_transient('moj_intro_admin_notice_dismissed')) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue']);
            add_action('admin_notices', [$this, 'content']);
        }
    }

    public function enqueue()
    {
        //wp_enqueue_script('jquery');
        wp_enqueue_script('intro_admin_popup', Helper()->js_path($this->parent_path) . 'popup.js', array('jquery'),
            '1.0', true);
    }

    public function content()
    {
        echo '<div class="moj-intro-notice update-nag notice is-dismissible">
                  <p><strong>Did you know?</strong>... You can <a href="/wp-admin/">find your website support contact in your Dashboard</a></p>
              </div>';
    }

    /**
     * do not show the notice after dismiss for a month
     */
    public function timer()
    {
        return set_transient('moj_intro_admin_notice_dismissed', true, 30 * DAY_IN_SECONDS);
    }

    public function timer_clear()
    {
        delete_transient('moj_intro_admin_notice_dismissed');
    }
}

function Popup()
{
    return new Popup();
}
