<?php

/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-09-23
 * Time: 15:31
 */

namespace MOJComponents\Introduce;

use MOJComponents\Introduce\PopupSettings as Settings;

/**
 * Suppress all rules containing TooManyPublicMethods in this
 * class
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Popup
{
    /**
     * @var bool
     */
    public $clearTimer = 1;

    /**
     * @var string
     */
    public $parentPath = '';

    /**
     * @var Helper
     */
    public $helper;

    /**
     * @var \WP_User
     */
    public $user;

    /**
     * @var array
     */
    public $message = [];

    /**
     * @var boolean
     */
    public $hasSettings = true;

    /**
     * @var object
     */
    public $settings;

    public function __construct()
    {
        global $mojHelper;
        $this->helper = $mojHelper;
        $this->settings = new Settings();
    }

    public function init()
    {
        $this->actions();
    }

    public function actions()
    {
        add_action('wp_ajax_popup_dismissed', [$this, 'timer']);
        add_action('set_current_user', [$this, 'messages'], 9);
        add_action('set_current_user', [$this, 'userLoaded']);

        // settings section
        add_action('wp_loaded', [$this->settings, 'settings'], 1);
    }

    public function userLoaded()
    {
        if (!get_transient('moj_intro_admin_notice_dismissed_' . $this->getUser()->ID)) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue']);
            add_action('admin_notices', [$this, 'content']);
            return true;
        }
    }

    public function messages()
    {
        // set the current user object
        $this->setUser(wp_get_current_user());

        $options = get_option('moj_component_settings');

        $this->setMessageTitle(
            empty($options['popup_message_title'])
                ? 'did you know?...'
                : $options['popup_message_title']
        );

        $this->setMessageBody(
            empty($options['popup_message_body'])
                ? 'You can find information about <strong><em>technical support</em></strong> for this website <a href="/wp-admin/">in your Dashboard</a>'
                : $options['popup_message_body']
        );

        $cachedHash = get_option('moj_component_settings_popup_message_cache');

        // reload if changed
        if ($this->messageHashCompare($cachedHash) === false) {
            $this->clearTimer = 1;
            $this->clearTimer();
            update_option('moj_component_settings_popup_message_cache', $this->messageHash());
            return true;
        }
    }

    public function enqueue()
    {
        wp_enqueue_script(
            'intro_admin_popup',
            $this->helper->jsPath($this->parentPath) . 'popup.js',
            array('jquery'),
            '1.0',
            true
        );
    }

    public function content($forceDisplay = false)
    {
        if ($forceDisplay || !current_user_can('administrator')) {
            $firstName = (
            empty($this->getUser()->user_firstname)
                ? 'Good ' . $this->helper->getTimePeriod()
                : 'Hey ' . $this->getUser()->user_firstname
            );

            $avatar = get_avatar_url($this->getUser()->ID, ['size' => '88']);

            echo '<div class="moj-intro-notice notice-success update-nag notice is-dismissible">
                <div class="intro-notice-img-wrap">
                    <a href="/wp/wp-admin/profile.php" title="View My Profile">
                        <img src="' . $avatar . '" alt="" class="intro-notice-avitar" />
                    </a>
                </div>
                <div class="intro-notice-copy-wrap">
                    <h3 class="intro-notice-header">' . $firstName . ', ' . $this->getMessageTitle() . '</h3>
                    <p class="intro-notice-text">' . $this->getMessageBody() . '</p>
                </div>
            </div>';
        }
    }

    /**
     * Will return an md5 hash of the message title + body of the popup if $messageMatch === false
     * If $messageMatch is present, $hash is compared with $messageMatch and the status returned
     * @return bool|string
     */
    public function messageHash()
    {
        return md5($this->getMessageTitle() . $this->getMessageBody());
    }

    public function messageHashCompare($messageMatch)
    {
        return ($messageMatch === $this->messageHash());
    }

    /**
     * do not show the notice, dismiss for a month
     */
    public function timer()
    {
        set_transient('moj_intro_admin_notice_dismissed_' . $this->getUser()->ID, true, 30 * DAY_IN_SECONDS);
    }

    public function clearTimer()
    {
        if ($this->clearTimer) {
            delete_transient('moj_intro_admin_notice_dismissed_' . $this->getUser()->ID);
            return true;
        }
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function setMessageTitle($title)
    {
        $this->message['title'] = $title;
    }

    public function setMessageBody($body)
    {
        $this->message['body'] = $body;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getMessageTitle()
    {
        return $this->message['title'];
    }

    public function getMessageBody()
    {
        return $this->message['body'];
    }
}

function Popup()
{
    return new Popup();
}
