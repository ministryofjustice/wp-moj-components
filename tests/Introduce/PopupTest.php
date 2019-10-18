<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-10-02
 * Time: 16:16
 */

namespace component;

use component\Introduce\Popup;
use WP_Mock;

class PopupTest extends WP_Mock\Tools\TestCase
{
    public $intro;

    public $helper;

    public $popup;

    public function setUp(): void
    {
        \WP_Mock::setUp();
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
        \WP_Mock::expectActionAdded('wp_ajax_popup_dismissed', [$this->popup, 'timer']);
        \WP_Mock::expectActionAdded('set_current_user', [$this->popup, 'userLoaded']);

        $this->popup->actions();

        $this->assertActionsCalled();
    }


    public function testUserLoadedTransientFalse()
    {
        \WP_Mock::expectActionAdded('admin_enqueue_scripts', [$this->popup, 'enqueue']);
        \WP_Mock::expectActionAdded('admin_notices', [$this->popup, 'content']);

        $this->popup->user = new \stdClass();
        $this->popup->user->ID = 1;
        $this->popup->setUser($this->popup->user);

        \WP_Mock::userFunction('get_transient', [
            'times' => 1,
            'args' => [
                'moj_intro_admin_notice_dismissed_' . $this->popup->getUser()->ID
            ],
            'return' => false
        ]);

        $return = $this->popup->userLoaded();
        $this->assertActionsCalled();
        $this->assertTrue($return);
    }

    public function testUserLoadedTransientTrue()
    {
        $this->popup->user = new \stdClass();
        $this->popup->user->ID = 1;
        $this->popup->setUser($this->popup->user);

        \WP_Mock::userFunction('get_transient', [
            'times' => 1,
            'args' => [
                'moj_intro_admin_notice_dismissed_' . $this->popup->getUser()->ID
            ],
            'return' => true
        ]);

        $return = $this->popup->userLoaded();
        $this->assertNull($return);
    }

    public function testMessageNoChanged()
    {
        $options = [
            'popup_message_title' => 'My Sweet Title',
            'popup_message_body' => 'Some great content'
        ];

        $option_hash = md5($options['popup_message_title'] . $options['popup_message_body']);

        $this->popup->setMessageTitle($options['popup_message_title']);
        $this->popup->setMessageBody($options['popup_message_body']);

        $user = new \stdClass();
        $user->ID = 1;

        \WP_Mock::userFunction('wp_get_current_user', [
            'times' => 1,
            'return' => $user
        ]);

        \WP_Mock::userFunction('get_option', [
            'times' => 1,
            'args' => [
                'moj_component_settings'
            ],
            'return' => $options
        ]);

        \WP_Mock::userFunction('get_option', [
            'times' => 1,
            'args' => [
                'moj_component_settings_popup_message_cache'
            ],
            'return' => $option_hash
        ]);

        $result = $this->popup->messages();

        $this->assertNull($result);
    }

    public function testMessageHasChanged()
    {
        $options = [
            'popup_message_title' => 'My test title',
            'popup_message_body' => 'Some great text'
        ];

        $option_hash = md5('This is my different title' . 'My new body');

        $user = new \stdClass();
        $user->ID = 1;

        \WP_Mock::userFunction('wp_get_current_user', [
            'times' => 1,
            'return' => $user
        ]);

        \WP_Mock::userFunction('get_option', [
            'times' => 1,
            'args' => [
                'moj_component_settings'
            ],
            'return' => $options
        ]);

        \WP_Mock::userFunction('get_option', [
            'times' => 1,
            'args' => [
                'moj_component_settings_popup_message_cache'
            ],
            'return' => $option_hash
        ]);

        \WP_Mock::userFunction('delete_transient', [
            'times' => 1,
            'args' => [
                'moj_intro_admin_notice_dismissed_1'
            ]
        ]);

        \WP_Mock::userFunction('update_option', [
            'times' => 1,
            'return' => true
        ]);

        $result = $this->popup->messages();

        $this->assertTrue($result);
    }

    public function testEnqueue()
    {
        \WP_Mock::userFunction('wp_enqueue_script', [
            'times' => 1,
            'args' => [
                'intro_admin_popup',
                'js/popup.js',
                array('jquery'),
                '1.0',
                true
            ],
            'return' => null
        ]);

        $return = $this->popup->enqueue();
        $this->assertNull($return);
    }

    public function testContentNoFirstName()
    {
        $output = $this->contentPrep('');

        $this->assertStringContainsString('Good', $output); // shows first_name not present.
        $this->assertStringContainsString(' src="https://example.com/path-to-my/avatar"', $output);
    }

    public function testContentWithFirstName()
    {
        $output = $this->contentPrep('Justice on the Web');
        $this->assertStringContainsString('Hey Justice on the Web', $output);
    }

    public function testClearTimerNo()
    {
        $this->clearTimerPrep(0);

        $return = $this->popup->clearTimer();
        $this->assertNull($return);
    }

    public function testClearTimerYes()
    {
        $this->clearTimerPrep(1);

        $return = $this->popup->clearTimer();
        $this->assertTrue($return);
    }

    private function clearTimerPrep($clear = 0)
    {
        $user = new \stdClass();
        $user->ID = 1;
        $this->popup->clearTimer = $clear;

        $this->popup->setUser($user);

        \WP_Mock::userFunction('delete_transient', [
            'times' => $clear,
            'args' => [
                'moj_intro_admin_notice_dismissed_1'
            ]
        ]);
    }

    private function contentPrep($first_name = '')
    {
        $user = new \stdClass();
        $user->ID = 1;
        $user->user_firstname = $first_name;

        $this->popup->setUser($user);
        $this->popup->setMessageTitle('My title');
        $this->popup->setMessageBody('My body');

        \WP_Mock::userFunction('get_avatar_url', [
            'times' => 1,
            'args' => [
                1,
                ['size' => '88']
            ],
            'return' => 'https://example.com/path-to-my/avatar'
        ]);

        ob_start(null);
        $this->popup->content();
        return ob_get_clean();
    }
}
