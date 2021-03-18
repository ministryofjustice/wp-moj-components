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

namespace MOJComponents\Introduce;

use MOJComponents\Helper;
use MOJComponents\Introduce\Popup as Popup;

class Introduce
{
    private $helper;

    public function __construct()
    {
        global $mojHelper;
        $this->helper = $mojHelper;

        $this->actions();


        // add popup
        $this->popup();
    }

    public function actions()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('wp_dashboard_setup', [$this, 'dashboardWidgets'], 1);
    }

    public function enqueue()
    {
        wp_enqueue_style('introduce_admin_css', $this->helper->cssPath(__FILE__) . 'main.css', []);
    }

    public function dashboardWidgets()
    {
        wp_add_dashboard_widget(
            'moj_support_widget',
            'Contact Us',
            [$this, 'dashboardWidgetSupportBox']
        );
    }

    public function dashboardWidgetSupportBox()
    {
        $image = '<img src="' . $this->helper->imagePath(__FILE__) . 'moj-dt.png' . '" alt="" class="dash-widget-image" />';
        echo '<div class="wp-clearfix">' . $image . '
                <p>This website is technically maintained by MoJ Digital & Technology, 
                   Justice on the Web team: </p>
                <p>&nbsp;&nbsp;&nbsp;<span class="dashicons dashicons-admin-home" title="Address"></span>&nbsp;&nbsp; 102 Petty France, 11.53.<br>
                &nbsp;&nbsp;&nbsp;<span class="dashicons dashicons-clock" title="Working Hours"></span>&nbsp;&nbsp; Monday - Friday 9:00 - 17:00<br/>
                   &nbsp;&nbsp;&nbsp;<span class="dashicons dashicons-email" title="Email Address"></span>&nbsp;&nbsp;
                   <a href="mailto:justice.web@digital.justice.gov.uk">justice.web@digital.justice.gov.uk</a></p>            
              </div>';
    }

    public function popup()
    {
        $popup = new Popup();
        $popup->parentPath = __FILE__;
        $popup->init();
    }
}
