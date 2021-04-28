<?php
/**
 *
 * Code based on:
 * Plugin Name:       Fast User Switching
 * Author:            Tikweb
 * Author URI:        http://www.tikweb.dk/
 */

namespace MOJComponents\Users;

class UserSwitch
{
    private $helper;

    /**
     * Register all the hooks and filters for the plugin
     */
    public function __construct()
    {

        global $mojHelper;
        $this->helper = $mojHelper;

        add_action('init', array($this, 'init'));


    }//End of __construct

    public function init()
    {

        $options = get_option('moj_component_settings');

        if(empty($options['user_switch_active']) == false) {
            $this->hooks();

            if (!$this->roleCanImpersonate()) return;

            if (isset($_GET['impersonate']) && !empty($_GET['impersonate'])) {
                $this->impersonate($_GET['impersonate']);
            }
        }
    }

    public function hooks()
    {
        add_action('wp_logout', array($this, 'unimpersonate'), 1);

        add_action('admin_bar_menu', array($this, 'changeLogoutText'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));

        add_action('wp_before_admin_bar_render', array($this, 'adminbarRender'), 9999, 1);

        add_action('wp_ajax_userswitch_user_search', array($this, 'ajaxUserSearch'));
        add_action('wp_ajax_nopriv_userswitch_user_search', array($this, 'ajaxUserSearch'));

        if (!$this->roleCanImpersonate()) return;

        // Add a column to the user list table which will allow you to impersonate that user
        add_filter('manage_users_columns', array($this, 'userTableColumns'));
        add_action('manage_users_custom_column', array($this, 'userTableColumnsValue'), 10, 3);

    }

    /**
     * Add an additional column to the users table
     * @param $columns - An array of the current columns
     */
    public function userTableColumns($columns)
    {
        $columns['usw_Impersonate'] = __('Switch user', 'wp-moj-components');
        return $columns;
    }

    /**
     * Return the value for custom columns
     * @param String $value - Current value, not used
     * @param String $column - The name of the column to return the value for
     * @param Integer $user_id - The ID of the user to return the value for
     * @return String
     */
    function userTableColumnsValue($value, $column, $user_id)
    {
        switch ($column) {
            case 'usw_Impersonate':
                $impersonate_url = admin_url("?impersonate=$user_id");
                return "<a href='$impersonate_url'>" . __('Switch user', 'wp-moj-components') . "</a>";
            default:
                return $value;
        }
    }

    public function saveRecentUser($user)
    {

        $user_id = get_current_user_id();

        if (current_user_can('manage_options')) {
            $recent_user_opt = get_option('switchuser_recent_imp_users', []);
        } else {
            $recent_user_opt = get_user_meta($user_id, 'switchuser_recent_imp_users', true);

            if (empty($recent_user_opt)) {
                $recent_user_opt = [];
            }
        }

        $wp_date_format = get_option('date_format');


        $roles = $this->getReadableRolename(array_shift($user->roles));


        $name_display = $user->first_name . ' ' . $user->last_name;

        $role_display = $roles;

        $role_display .= ' - ' . $user->user_login;

        $date_display = date($wp_date_format);

        $keep = $user->data->ID . '&' . $name_display . '&' . $role_display . '&' . $date_display;

        if (!in_array($keep, $recent_user_opt)) {
            array_unshift($recent_user_opt, $keep);
        }

        if (in_array($keep, $recent_user_opt) && $recent_user_opt[0] !== $keep) {
            $key = array_search($keep, $recent_user_opt);
            unset($recent_user_opt[$key]);
            array_unshift($recent_user_opt, $keep);
        }

        $recent_user_opt = array_slice($recent_user_opt, 0, 5);

        if (current_user_can('manage_options')) {
            update_option('switchuser_recent_imp_users', $recent_user_opt);
        } else {
            update_user_meta($user_id, 'switchuser_recent_imp_users', $recent_user_opt, '');
        }

    }//End saveRecentUser

    /**
     * Get get user id and switch to
     */
    public function impersonate($user_id)
    {


        global $current_user;

        $block_attempt = false;
        $user_id = $_GET['impersonate'];
        $user = get_userdata($user_id);

        if ($user == false) {

            $block_attempt = true;

        }

        if (!current_user_can('manage_options')) {

            if (in_array('administrator', (array)$user->roles)) {
                $block_attempt = true;
            }

        }

        if ($block_attempt === true) {
            $redirect = add_query_arg('impna', 'true2', admin_url());
            return wp_redirect($redirect);
        }

        $this->saveRecentUser($user);

        // We need to know what user we were before so we can go back
        $hashed_id = $this->encryptDecrypt('encrypt', $current_user->ID);
        setcookie('impersonated_by_' . COOKIEHASH, $hashed_id, 0, SITECOOKIEPATH, COOKIE_DOMAIN, false, true);

        // Login as the other user
        wp_set_auth_cookie($user_id, false);

        // If impresonate user is vendor than set vendor cookies.
        if (class_exists('WC_Product_Vendors_Utils')) {
            if (WC_Product_Vendors_Utils::is_vendor($user_id)) {
                $vendor_data = WC_Product_Vendors_Utils::get_all_vendor_data($user_id);
                $vendor_id = key($vendor_data);
                setcookie('woocommerce_pv_vendor_id_' . COOKIEHASH, absint($vendor_id), 0, SITECOOKIEPATH, COOKIE_DOMAIN);
            }
        }//End if

        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {

            $redirect_url = $_SERVER['HTTP_REFERER'];

            if (strpos($redirect_url, '/wp-admin/') != false) {
                $redirect_url = admin_url();
            }

        } else {

            $redirect_url = admin_url();

        }

        // add impersonatting param with url to detect this request is impersonatting.
        $redirect_url = $redirect_url . '?imp=true';


        wp_redirect($redirect_url);
        exit;

    }//End impersonate

    /**
     * Switch back to old user
     */
    public function unimpersonate()
    {
        $impersonated_by = self::impersonatedBy();
        if (!empty($impersonated_by)) {
            wp_set_auth_cookie($impersonated_by, false);
            // Unset the cookie
            setcookie('impersonated_by_' . COOKIEHASH, 0, time() - 3600, SITECOOKIEPATH, COOKIE_DOMAIN, false, true);

            if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
                $redirect_url = $_SERVER['HTTP_REFERER'];
            } else {
                $redirect_url = admin_url();
            }

            wp_redirect($redirect_url);
            exit;
        }
    }//End unimpersonate

    /**
     * Get impersonated user from cookie
     */
    private static function impersonatedBy()
    {
        $key = 'impersonated_by_' . COOKIEHASH;
        if (isset($_COOKIE[$key]) && !empty($_COOKIE[$key])) {
            $user_id = self::encryptDecrypt('decrypt', $_COOKIE[$key]);
            return $user_id;
        } else {
            return false;
        }
    }//impersonatedBy

    /**
     * Change logout text
     */
    public static function changeLogoutText($wp_admin_bar)
    {
        // If user is impersonating, change the logout text
        $impersonatedBy = self::impersonatedBy();
        if (!empty($impersonatedBy)) {
            $args = array(
                'id' => 'logout',
                'title' => __('Switch to own user', 'wp-moj-components'),
                'meta' => array('class' => 'logout')
            );
            $wp_admin_bar->add_node($args);
        }
    }//End changeLogoutText

    /**
     * Encript and Decrypt
     */
    private static function encryptDecrypt($action, $string)
    {
        $output = false;
        $encrypt_method = "AES-256-CBC";

        //$secret_key = 'This is fus hidden key';
        //$secret_iv = 'This is fus hidden iv';

        $secret_key = wp_salt();
        $secret_iv = wp_salt('secure_auth');

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }// End encryptDecrypt

    /**
     * plugin script to be enqueued in admin and frontend.
     * @return [type] [description]
     */
    public function enqueue()
    {
        wp_enqueue_style('userswitch_admin_css', $this->helper->cssPath(__FILE__) . 'main.css', []);
        wp_enqueue_script('userswitch_search_scroll', $this->helper->jsPath(__FILE__) . 'jquery.nicescroll.min.js', array('jquery'), '1.1', true);
        wp_enqueue_script('userswitch_script', $this->helper->jsPath(__FILE__) . 'user-switch.js', array('jquery', 'userswitch_search_scroll'), '1.2', true);

        wp_localize_script('userswitch_script', 'ScriptData', array('ajaxurl' => admin_url('admin-ajax.php')));
    }


    /**
     * Return list of impersonated recent users list.
     * @return string [description]
     */
    public function impersonateUserList()
    {

        $ret = '';

        if (current_user_can('manage_options')) {
            $opt = get_option('switchuser_recent_imp_users', []);
        } else {
            $opt = get_user_meta(get_current_user_id(), 'switchuser_recent_imp_users', true);
        }


        if (!empty($opt)) {
            foreach ($opt as $key => $value) {

                $user_role_display = '';

                $user = explode('&', $value);
                $user = array_filter($user);
                $user_id = isset($user[0]) ? $user[0] : '';
                $user_name = isset($user[1]) ? trim($user[1]) : '';
                $user_role = isset($user[2]) ? trim($user[2]) : '';
                $last_login_date = isset($user[3]) ? trim($user[3]) : '';

                if (!empty($user_name) && !empty($user_role)) {
                    $user_role_display = sprintf('( %s )', $user_role);
                } else {

                    $rc = explode('-', $user_role);
                    $rc = array_map('trim', $rc);
                    $rc = array_filter($rc);

                    if (count($rc) < 2) {
                        $user_role = str_replace('-', '', $user_role);
                    }

                    $user_role_display = $user_role;
                }

                if (!empty($last_login_date) && isset($fus_settings['fus_showdate'])) {
                    $last_login_date = sprintf('<span class="small-date">%s</span>', $last_login_date);
                } else {
                    $last_login_date = '';
                }

                $ret .= '<a href="' . admin_url("?impersonate=$user_id") . '">' . $user_name . ' ' . $user_role_display . ' ' . $last_login_date . '</a>' . PHP_EOL;


            }
        }

        return $ret;
    }


    public function userCanSwitch($user_data = null)
    {

        $can_switch = false;

        // check if admin , directy give him access
        if (current_user_can('manage_options')) {
            return true;
        }

        // if no user_data passed to function, get user data.
        if (empty($user_data)) {
            $user_data = wp_get_current_user();
        }

        // if user isn't exists ( case visitor ) return false
        if (!$user_data->exists()) {
            return false;
        }

        if (current_user_can('edit_users') || current_user_can('list_users')) {
            return true;
        }

    }

    public function roleCanImpersonate()
    {


        if (current_user_can('manage_options')) {
            return true;
        }

        $cur_user = wp_get_current_user();

        if (!$cur_user->exists()) {
            return false;
        }

        $settings = get_option('fus_settings');

        if (isset($settings['fus_roles']) && !empty($settings['fus_roles'])) {


            $cur_user_roles = (array)$cur_user->roles;
            $matched = array_intersect($cur_user_roles, $settings['fus_roles']);

            if (count($matched) > 0) {
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }

    }


    /**
     * Render user search function in wp admin bar.
     */
    public function adminbarRender()
    {

        // if admin_bar is showing.
        if (is_admin_bar_showing()) {

            global $wp_admin_bar;

            // if current user can edit_users than he can see this.
            if ($this->roleCanImpersonate()) {

                $wp_admin_bar->add_menu(
                    array(
                        'id' => 'tikemp_impresonate_user',
                        'title' => __('Switch user', 'wp-moj-components'),
                        'href' => '#',
                    )
                );

                // search form
                $html = '<div id="userswitch_search">';
                $html .= '<form action="#" method="POST" id="userswitch_usearch_form" class="clear">';
                $html .= '<input type="text" name="userswitch_username" id="userswitch_username" placeholder="' . __('Username or ID', 'wp-moj-components') . '">';
                $html .= '<input type="submit" value="' . __('Search', 'wp-moj-components') . '" id="userswitch_search_submit">';
                $html .= '<input type="hidden" name="userswitch_search_nonce" value="' . wp_create_nonce("userswitch_search_nonce") . '">';
                $html .= '<div class="wp-clearfix"></div>';
                $html .= '</form>';
                $html .= '<div id="userswitch_usearch_result"></div>';
                $html .= '<div id="userswitch_recent_users">';
                $html .= '<strong>' . __('Recent Users', 'wp-moj-components') . '</strong>';
                $html .= '<hr>' . $this->impersonateUserList();
                $html .= '</div>';

                $wp_admin_bar->add_menu(
                    array(
                        'id' => 'tikemp_impresonate_user_search',
                        'parent' => 'tikemp_impresonate_user',
                        'title' => $html,
                    )
                );

            }//if(current_user_can('manage_optiona'))

        }//if(is_admin_bar_showing())
    }

    /**
     * User search on ajax request
     */
    public function ajaxUserSearch()
    {

        $query = isset($_POST['username']) ? trim($_POST['username']) : '';
        $nonce = $_POST['nonce'];

        if (!wp_verify_nonce($nonce, 'userswitch_search_nonce')) {
            exit();
        }

        $args = array(
            'search' => is_numeric($query) ? $query : '*' . $query . '*'
        );

        if (!is_email($query) && strpos($query, '@') !== false) {
            $args['search_columns'] = ['user_login', 'user_email'];
        }

        if (!current_user_can('manage_options')) {
            $args['role__not_in'] = 'Administrator';
        }

        $user_query = new \WP_User_Query($args);

        $ret = '';

        $site_roles = $this->getRoles();

        if (!empty($user_query->results)) {

            foreach ($user_query->results as $user) {

                if ($user->ID == get_current_user_id()) {
                    continue;
                }


                $name_display = $user->first_name . ' ' . $user->last_name;
                $user_role_display = ' (' . $site_roles[array_shift($user->roles)] . ' - ' . $user->user_login . ')';


                $ret .= '<a href="' . admin_url("?impersonate={$user->ID}") . '">' . $name_display . ' ' . $user_role_display . '</a>' . PHP_EOL;
            }
        } else {
            $ret .= '<strong>' . __('No user found!', 'wp-moj-components') . '</strong>' . PHP_EOL;
        }

        echo $ret;
        die();
    }

    /**
     * Get site user roles
     * @return array array of roles and capabilities.
     */
    function getRoles()
    {

        $all_roles = wp_roles()->roles;

        $return_array = [];

        foreach ($all_roles as $key => $role) {
            $return_array[$key] = $role['name'];
        }

        return $return_array;
    }

    /**
     * Return readable rolename
     */
    public function getReadableRolename($role)
    {
        $all_roles = $this->getRoles();

        $ret = isset($all_roles[$role]) ? $all_roles[$role] : 'subscriber';

        return $ret;
    }

} // Class end