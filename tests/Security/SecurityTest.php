<?php

namespace MOJComponents;

use WP_Mock;

class SecurityTest extends WP_Mock\Tools\TestCase
{
    private $helper;

    private $security;

    public function setUp(): void
    {
        \WP_Mock::setUp();
        $this->security = new Security();
    }

    public function tearDown(): void
    {
        \WP_Mock::tearDown();
        $this->security = null;
    }

    /*public function testActions()
    {
        \WP_Mock::expectActionAdded('admin_enqueue_scripts', [$this->security, 'enqueue']);

        $this->security->actions();

        parent::assertActionsCalled();
    }*/

    public function testHelperVar()
    {
        $this->assertIsObject($this->security->helper);
    }

    /*public function testEnqueue()
    {
        \WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 1,
            'args' => [
                'security_admin_css',
                'css/main.css',
                []
            ]
        ]);

        $this->security->enqueue();
        $this->assertIsCallable('wp_enqueue_style');
    }*/
}
