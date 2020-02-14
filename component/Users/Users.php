<?php

namespace component;

use component\Users\UsersSettings as Settings;

class Users
{
    private $helper;

    /**
     * @var boolean
     */
    public $hasSettings = true;

    /**
     * @var object
     */
    public $settings;

    public $text_domain = 'moj-comp-user-login';

    public function __construct()
    {
        // make sure cron can pick this up
        $this->helper = new Helper();
        $this->addSchedules();
        $this->actions();
    }

    public function actions()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('wp_login', [$this, 'wpLogin']);
        add_action('moj_check_user_activity', [$this, 'inactiveUsers']);

        // settings section
        add_action('wp_loaded', [new Settings(), 'settings'], 1);
    }

    public function enqueue()
    {
    }

    public function inactiveUsers()
    {
        // can we run?
        $options = get_option('moj_component_settings');
        if (isset($options['user_active_disable']) && $options['user_active_disable'] === 'yes') {
            return false;
        }

        // get users
        $users = get_users('role=web-administrator');

        $inactive_users = [];
        foreach ($users as $user) {
            $last_login = get_user_meta($user->ID, $this->text_domain);
            $three_months_ago = time() - 7776000; // 3 months in seconds
            if ($last_login < $three_months_ago) {
                $inactive_users[] = [
                    'name' => $user->display_name,
                    'email' => $user->user_email,
                    'last_login' => date("l jS \of F", $last_login)
                ];
            }
        }

        $inactive_users[] = [
            'name' => 'Damien',
            'email' => 'Damien@me.com',
            'last_login' => date("l jS \of F", time())
        ];

        $inactive_users[] = [
            'name' => 'Hazel',
            'email' => 'hazel@me.com',
            'last_login' => date("l jS \of F", time())
        ];

        if (!empty($inactive_users)) {
            $message = '';
            foreach ($inactive_users as $user) {
                $message .= '<a href="mailto:' . $user['email'] . '">' . $user['name'] . '</a> last logged in on ' . $user['last_login'] . '<br>- - -<br>';
            }

            $siteName = get_option('blogname');
            $message = $this->getMailHTML($message, $siteName, count($inactive_users));

            // prepare an email.
            $subject = '[USERS] Inactive user report for ' . $siteName;
            $this->helper->setMailSubject($subject);
            $this->helper->setMailMessage($message);
            $this->helper->setMaiTo($options['user_active_to_email'] ?? '');

            $this->helper->mail();
        }
    }

    public function getMailHTML($user_list, $site, $nth_users)
    {
        $emailTemplate = file_get_contents(__DIR__ . '/assets/email-templates/moj-users.html');

        $search = [
            '{blogname}',
            '{dt-logo}',
            '{list_of_users}',
            '{nth_users}',
            '{domain}'
        ];

        $replace = [
            $site,
            $this->helper->imagePath(__FILE__) . 'moj-dt.png',
            $user_list,
            $nth_users,
            get_home_url()
        ];

        return str_replace($search, $replace, $emailTemplate);
    }

    /**
     * @param $user_login
     */
    public function wpLogin($user_login)
    {
        $user = get_user_by('login', $user_login);
        update_user_meta($user->ID, $this->text_domain, time());
    }

    /**
     * accessed on-load
     */
    public function addSchedules()
    {
        $recurrence = get_option('moj_component_settings', array()); // default to monthly

        if ($recurrence !== wp_get_schedule('moj_check_user_activity')) {
            $recurrence['user_inactive_schedule'] = 'monthly';
        }

        // schedules
        if (!wp_next_scheduled('moj_check_user_activity')) {
            wp_schedule_event(time(), $recurrence['user_inactive_schedule'], 'moj_check_user_activity');
        }
    }
}
