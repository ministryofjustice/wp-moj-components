<?php

namespace MOJComponents\Users;

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

    /**
     * @var string
     * Underscore at the beginning of key hides name/value from GUI
     */
    public $last_logged_in_key = '_moj_comp_user_login';

    public function __construct()
    {
        global $mojHelper;
        $this->helper = $mojHelper;

        // make sure cron can pick this up
        $this->addSchedule();
        $this->actions();

        $this->userSwitch();
    }

    public static function userSwitch()
    {
        return new UserSwitch();
    }

    public function actions()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('wp_login', [$this, 'wpLogin']);
        add_action('moj_check_user_activity', [$this, 'inactiveUsers']);

        // settings section
        add_action('wp_loaded', [new UsersSettings(), 'settings'], 1);
    }

    public function enqueue()
    {
    }

    public function inactiveUsers()
    {
        // can we run? A dashboard setting may have turned off processing.
        $options = get_option('moj_component_settings');
        if (isset($options['user_active_disable']) && $options['user_active_disable'] === 'yes') {
            return false;
        }

        // get users
        $users = get_users('role=web-administrator');

        $inactive_users = [];
        foreach ($users as $user) {
            $last_login = get_user_meta($user->ID, $this->last_logged_in_key, true);
            // if no logged in timestamp for user, create one and continue to next user.
            if ($last_login === '') {
                update_user_meta($user->ID, $this->last_logged_in_key, time());
                update_user_meta($user->ID, $this->last_logged_in_key . '_source', 'system');
                continue;
            }

            // cast to int
            $last_login = (int)$last_login;

            $three_months_ago = time() - 7776000; // 3 months in seconds
            if ($last_login < $three_months_ago) {
                $inactive_users[] = [
                    'name' => $user->display_name,
                    'profile' => $this->getUserProfileURL($user->ID),
                    'last_login' => date("l jS \of F", $last_login),
                    'source' => get_user_meta($user->ID, $this->last_logged_in_key . '_source', true)
                ];
            }
        }

        if ($options['user_inactive_test']) {
            foreach ($this->dummyTestData() as $dummy_user) {
                $inactive_users[] = $dummy_user;
            }
        }

        if (!empty($inactive_users)) {
            $message = '';
            foreach ($inactive_users as $user) {
                $source = ' <small style="color:#666666;">(source: ' . $user['source'] . ')</small>';
                $message .= '<a href="' . $user['profile'] . '" title="Visit profile">' . $user['name'] . '</a> last logged in on ' . $user['last_login'] . $source . '<br>- - -<br>';
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

    private function dummyTestData()
    {
        return [
            [
                'name' => 'Beverley',
                'profile' => $this->getUserProfileURL(1),
                'last_login' => date("l jS \of F", time()),
                'source' => 'system'
            ],
            [
                'name' => 'Robert',
                'profile' => $this->getUserProfileURL(2),
                'last_login' => date("l jS \of F", time()),
                'source' => 'system'
            ],
            [
                'name' => 'Adam',
                'profile' => $this->getUserProfileURL(3),
                'last_login' => date("l jS \of F", time()),
                'source' => 'user'
            ],
        ];
    }

    /**
     * @param $user_id
     * @return string|void
     */
    public function getUserProfileURL($user_id)
    {
        return admin_url('user-edit.php?user_id=' . $user_id);
    }

    public function getMailHTML($user_list, $site, $nth_users)
    {
        $emailTemplate = file_get_contents(__DIR__ . '/assets/email-templates/moj-users.html');

        $search = [
            '{blogname}',
            '{dt-logo}',
            '{list_of_users}',
            '{nth_users}',
            '{domain}',
            '{moj-logo}'
        ];

        $replace = [
            $site,
            $this->helper->imagePath(__FILE__) . 'moj-dt.png',
            $user_list,
            $nth_users,
            get_home_url(),
            $this->helper->imagePath(__FILE__) . 'moj.png'
        ];

        return str_replace($search, $replace, $emailTemplate);
    }

    /**
     * @param $user_login
     */
    public function wpLogin($user_login)
    {
        $user = get_user_by('login', $user_login);
        update_user_meta($user->ID, $this->last_logged_in_key, time());
        update_user_meta($user->ID, $this->last_logged_in_key . '_source', 'user');
    }

    /**
     * accessed on-load
     */
    public function addSchedule()
    {
        // Is this feature disabled in the settings?
        $options = get_option('moj_component_settings');
        if (isset($options['user_active_disable']) && $options['user_active_disable'] === 'yes') {
            return false;
        }

        $recurrence = get_option('moj_component_settings', array()); // default to monthly
        $recurrence = $recurrence['user_inactive_schedule'] ?? 'monthly';
        $now_recurrence = wp_get_schedule('moj_check_user_activity');

        if ($now_recurrence && $recurrence !== $now_recurrence) {
            wp_clear_scheduled_hook('moj_check_user_activity');
        }

        // schedules
        if (!wp_next_scheduled('moj_check_user_activity')) {
            wp_schedule_event(time(), $recurrence, 'moj_check_user_activity');
        }
    }
}
