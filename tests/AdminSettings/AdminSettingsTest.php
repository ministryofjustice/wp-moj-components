<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-10-07
 * Time: 11:34
 */

namespace component;

use WP_Mock;
use component\Security\VulnerabilityDBSettings;

class AdminSettingsTest extends WP_Mock\Tools\TestCase
{
    public $settings;
    public $adminSettings;
    public $helper;

    public function setUp(): void
    {
        \WP_Mock::setUp();

        global $mojHelper;
        $this->helper = $mojHelper;

        $this->adminSettings = new AdminSettings();
    }

    public function tearDown(): void
    {
        \WP_Mock::tearDown();
        $this->adminSettings = null;
    }

    public function testEnqueue()
    {
        $path = str_replace(['/tests/AdminSettingsTest', 'tests'], ['component/AdminSettings', 'component'], __DIR__);

        \WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 1,
            'args' => [
                'settings_admin_css',
                $path . '/assets/css/main.css',
                []
            ]
        ]);

        \WP_Mock::userFunction('wp_enqueue_script', [
            'times' => 1
        ]);

        \WP_Mock::userFunction('plugins_url', [
            'times' => 2,
            'return' => $path . '/assets/'
        ]);

        $this->adminSettings->enqueue();
        $this->assertIsCallable('wp_enqueue_style');
    }

    public function testPage()
    {
        \WP_Mock::userFunction('add_options_page', [
            'times' => 1,
            'args' => [
                'MoJ Component Settings Page',
                'MoJ Components',
                'manage_options',
                'mojComponentSettings',
                [$this->adminSettings, 'content']
            ]
        ]);

        $result = $this->adminSettings->page();
        $this->assertNull($result);
    }

    public function testSettings()
    {
        $class_one = new VulnerabilityDBSettings();

        $class_two = new \stdClass();

        $settings_array = [
            $class_one,
            $class_two
        ];

        \WP_Mock::userFunction('register_setting', [
            'times' => 1,
            'args' => [
                'mojComponentSettings',
                'moj_component_settings'
            ],
            'return' => null
        ]);

        \WP_Mock::userFunction('add_settings_section', [
            'times' => 1,
            'args' => [
                'component-tab-0',
                'Vulnerability DB Settings',
                [$class_one, 'settingsSectionCB'],
                'mojComponentSettings'
            ],
            'return' => null
        ]);

        \WP_Mock::userFunction('add_settings_field', [
            'times' => 1,
            'args' => [
                'vulndb_token',
                'API Key',
                [$class_one, 'vulndbServiceTokenCB'],
                'mojComponentSettings',
                'component-tab-0'
            ]
        ]);

        \WP_Mock::userFunction('add_settings_field', [
            'times' => 1,
            'args' => [
                'to_email',
                'Notification email',
                [$class_one, 'notificationEmailAddress'],
                'mojComponentSettings',
                'component-tab-0'
            ]
        ]);

        \WP_Mock::userFunction('add_settings_field', [
            'times' => 1,
            'args' => [
                'force_collect',
                'Force check?',
                [$class_one, 'forceDataCollect'],
                'mojComponentSettings',
                'component-tab-0'
            ]
        ]);

        // setup array of objects
        $this->helper->adminSettings = $settings_array;

        // begin test
        $result = $this->adminSettings->settings(); // returns tabs

        // assert
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }
}
