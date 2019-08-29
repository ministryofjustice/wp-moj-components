<?php
/**
 * Plugin name: WP MoJ Components
 * Plugin URI:  https://github.com/ministryofjustice/wp-moj-components
 * Description: Introduces various functions that are commonly used across the MoJ network of sites
 * Version:     1.1.2
 * Author:      Ministry of Justice
 * Author URI:  https://peoplefinder.service.gov.uk/people/damien-wilson
 * License:     MIT License
 **/


/**
 * Get the current version of WP
 *
 * This is provided for external resources to resolve the current wp_version
 *
 * @return string
 */
if (!function_exists('moj_wp_version')) {
    function moj_wp_version()
    {
        global $wp_version;

        return $wp_version;
    }

    add_action('rest_api_init', function () {
        register_rest_route('moj', '/version', array(
            'methods' => 'GET',
            'callback' => 'moj_wp_version'
        ));
    });
}

if (!function_exists('moj_get_page_uri')) {
    function moj_get_page_uri()
    {
        global $wp;
        return home_url($wp->request);
    }
}

/**
 * creates dashboard widgets
 */
if (!function_exists('moj_dashboard_widgets')) {
    function moj_dashboard_widgets()
    {
        wp_add_dashboard_widget('moj_support_widget', 'Contact Us', 'moj_dashboard_widget_support_box');
    }
    add_action('wp_dashboard_setup', 'moj_dashboard_widgets', 1);
}

if (!function_exists('moj_dashboard_widget_support_box')) {
    function moj_dashboard_widget_support_box()
    {
        $image = '<img src="' . esc_url( plugins_url( 'assets/images/moj-dandt.png', __FILE__ ) ) . '" alt="" style="float:left;max-width:80px;height:auto;margin-right:20px" />';
        echo '<div class="wp-clearfix">
                '.$image.'
                <p>This website is technically maintained by MoJ Digital & Technology, Justice on the Web team @ 102 Petty France, 11.53.</p>
                <p>Working hours: Monday - Friday 9:00 - 17:00<br/>
                   Email: wordpress@digital.justice.gov.uk</p>            
              </div>';
    }






}

