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
    /**
     * @var bool
     */
    private $clear_timer = 1;

    /**
     * @var string
     */
    public $parent_path = '';

    /**
     * @var Helper
     */
    public $helper;

    /**
     * @var \WP_User
     */
    private $user;

    public function __construct()
    {
        $this->helper = new Helper();
    }

    public function init()
    {
        $this->add_action();
    }

    public function add_action()
    {
        add_action('wp_ajax_popup_dismissed', [$this, 'timer']);

        $this->maybe_clear_timer();

        if (!get_transient('moj_intro_admin_notice_dismissed')) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue']);
            add_action('admin_notices', [$this, 'content']);
        }
    }

    public function enqueue()
    {
        wp_enqueue_script('intro_admin_popup', $this->helper->js_path($this->parent_path) . 'popup.js', array('jquery'),
            '1.0', true);
    }

    public function content()
    {
        $this->user = \wp_get_current_user();
        $first_name = (empty($this->user->user_firstname) ? 'Good ' . $this->helper->get_time_period() : 'Hey ' . $this->user->user_firstname );
        $avitar = get_avatar_url($this->user->ID, ['size' => '88']);
        echo '<div class="moj-intro-notice update-nag notice is-dismissible">

                 <div class="intro-notice-img-wrap">
                    <a href="/wp/wp-admin/profile.php" title="View My Profile"><img src="' . $avitar . '" alt="" class="intro-notice-avitar" /></a>
                 </div>
                 
                 <div class="intro-notice-copy-wrap">
                    <h3 class="intro-notice-header">' . $first_name . ', did you know?...</h3>
                    <p class="intro-notice-text">You can find information about <strong><em>technical support</em></strong> for this website <a href="/wp-admin/">in your Dashboard</a></p>
                 </div>
              </div>';
    }

    /**
     * do not show the notice, dismiss for a month
     */
    public function timer()
    {
        set_transient('moj_intro_admin_notice_dismissed', true, 30 * DAY_IN_SECONDS);
    }

    public function maybe_clear_timer()
    {
        if ($this->clear_timer) {
            delete_transient('moj_intro_admin_notice_dismissed');
        }
    }
}

function Popup()
{
    return new Popup();
}
