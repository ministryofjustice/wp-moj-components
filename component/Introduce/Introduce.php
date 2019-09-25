<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-09-23
 * Time: 12:06
 *
 * Introduce is all about letting our stakeholders know who we are.
 * This class includes a dashboard widget and a popup
 */

namespace component;


class Introduce
{
    private $helper;

    public function __construct()
    {
        $this->add_actions();
        $this->helper = new Helper();

        // add popup
        $this->popup();
    }

    private function add_actions()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_dashboard_setup', [$this, 'dashboard_widgets'], 1);
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style( 'introduce_admin_css', $this->helper->css_path(__FILE__) . 'main.css', []);
    }

    public function dashboard_widgets()
    {
        wp_add_dashboard_widget(
            'moj_support_widget',
            'Contact Us',
            [$this, 'dashboard_widget_support_box']
        );
    }

    public function dashboard_widget_support_box()
    {
        $image = '<img src="' . $this->helper->image_path(__FILE__) . 'moj-dandt.png' . '" alt="" class="dash-widget-image" />';
        echo '<div class="wp-clearfix">' . $image . '
                <p>This website is technically maintained by MoJ Digital & Technology, 
                   Justice on the Web team: </p>
                <p>&nbsp;&nbsp;&nbsp;<span class="dashicons dashicons-admin-home" title="Address"></span>&nbsp;&nbsp; 102 Petty France, 11.53.<br>
                &nbsp;&nbsp;&nbsp;<span class="dashicons dashicons-clock" title="Working Hours"></span>&nbsp;&nbsp; Monday - Friday 9:00 - 17:00<br/>
                   &nbsp;&nbsp;&nbsp;<span class="dashicons dashicons-email" title="Email Address"></span>&nbsp;&nbsp;
                   <a href="mailto:wordpress@digital.justice.gov.uk">wordpress@digital.justice.gov.uk</a></p>            
              </div>';
    }

    public function popup()
    {
        $popup = Popup();
        $popup->parent_path = __FILE__;
        $popup->init();
    }
}
