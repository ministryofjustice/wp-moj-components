<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-10-02
 * Time: 16:16
 */

namespace MOJComponents\Introduce;

use WP_Mock;

class IntroduceTest extends WP_Mock\Tools\TestCase
{
    public $intro;

    public $popup;

    public function setUp(): void
    {
        \WP_Mock::setUp();
        $this->intro = new Introduce();
        $this->popup = new Popup();
    }

    public function tearDown(): void
    {
        \WP_Mock::tearDown();
        $this->helper = null;
        $this->popup = null;
    }

    public function testActions()
    {
        \WP_Mock::expectActionAdded('admin_enqueue_scripts', [$this->intro, 'enqueue']);
        \WP_Mock::expectActionAdded('wp_dashboard_setup', [$this->intro, 'dashboardWidgets'], 1);

        $this->intro->actions();

        parent::assertActionsCalled();
    }

    public function testEnqueue()
    {
        \WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 1,
            'args' => [
                'introduce_admin_css',
                'css/main.css',
                []
            ]
        ]);

        $this->intro->enqueue();
        $this->assertIsCallable('wp_enqueue_style');
    }

    public function testDashboardWidgets()
    {
        \WP_Mock::userFunction('wp_add_dashboard_widget', [
            'times' => 1,
            'args' => [
                'moj_support_widget',
                'Contact Us',
                [$this->intro, 'dashboardWidgetSupportBox']
            ]
        ]);

        $this->intro->dashboardWidgets();
        $this->assertIsCallable('wp_add_dashboard_widget');
    }

    public function testDashboardWidgetSupportBox()
    {
        ob_start(null);
        $this->intro->dashboardWidgetSupportBox();
        $output = ob_get_clean();

        $this->assertStringContainsString(
            'This website is technically maintained by MoJ Digital & Technology',
            $output
        );
    }
}
