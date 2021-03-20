<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-10-02
 * Time: 16:16
 */

namespace MOJComponents\Versions;

use WP_Mock;

define('DAY_IN_SECONDS', 123456789);

class PluginsTest extends WP_Mock\Tools\TestCase
{
    public $plugins;

    public function setUp() : void
    {
        \WP_Mock::setUp();
        $this->plugins = new Plugins();
    }

    public function tearDown() : void
    {
        \WP_Mock::tearDown();
        $this->plugins = null;
    }

    public function testActions()
    {
        \WP_Mock::expectActionAdded('after_setup_theme', [$this->plugins, 'load']);

        $this->plugins->actions();

        parent::assertActionsCalled();
    }

    public function testLoadGetTransientIsTrue()
    {
        \WP_Mock::userFunction('get_transient', [
            'times' => 1,
            'return' => true
        ]);

        $result = $this->plugins->load();

        $this->assertEquals(false, $result);
    }

    public function testLoadGetTransientIsFalse()
    {
        \WP_Mock::userFunction('get_transient', [
            'times' => 1,
            'return' => false
        ]);

        \WP_Mock::userFunction('get_plugins', [
            'times' => 1,
            'return' => [
                [
                    'Author' => 'Classic Editor',
                    'TextDomain' => 'classic-editor',
                    'Version' => '2.0',
                ],
                [
                    'Author' => 'Example Plugin Extreme',
                    'TextDomain' => 'example-plugin-extreme',
                    'Version' => '5.2.9',
                ],
                [
                    'Author' => '',
                    'TextDomain' => '',
                    'Version' => '0.0.0',
                ]
            ]
        ]);

        \WP_Mock::userFunction('set_transient', [
            'times' => 1,
            'return' => true
        ]);

        \WP_Mock::userFunction('update_option', [
            'times' => 1,
            'return' => true
        ]);

        $result = $this->plugins->load();

        // result is expected to be an array from the set() method
        $this->assertIsArray($result);
        // count was originally 3, function removed empty Author name. Expect 2.
        $this->assertCount(2, $result);
    }
}
