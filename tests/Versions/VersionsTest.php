<?php

/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-10-01
 * Time: 13:07
 */

namespace MOJComponents\Versions;

use WP_Mock;

class VersionsTest extends WP_Mock\Tools\TestCase
{
    public $versions;

    public function setUp(): void
    {
        \WP_Mock::setUp();
        $this->versions = new Versions();
    }

    public function tearDown(): void
    {
        \WP_Mock::tearDown();
        $this->versions = null;
    }

    public function testActions()
    {
        parent::assertActionsCalled();
    }

    private function registerApiCheck($apiRegister)
    {
        \WP_Mock::userFunction('register_rest_route', [
            'times' => 1,
            'return' => true
        ]);

        $theEndPoint = $this->versions->{$apiRegister}();

        $this->assertTrue($theEndPoint);
    }

    public function testRegisterWPVersionApiEndpoint()
    {

        $this->registerApiCheck('registerWPVersionApiEndpoint');
    }

    public function testRegisterPluginApiEndpoint()
    {
        $this->registerApiCheck('registerPluginApiEndpoint');
    }

    public function testWpVersion()
    {
        global $wp_version;
        $wp_version = 6;

        $version = $this->versions->wpVersion();
        $this->assertEquals(6, $version);
    }

    public function testPluginsProperty()
    {
        $plugins = $this->versions->plugins;
        $this->assertObjectHasAttribute('parentPath', $plugins);
    }

    public function testPluginVersions()
    {
        $args = [
            'my-awesome-plugin' => '1.2.3',
            'my-example-plugin' => '3.2.1'
        ];

        \WP_Mock::userFunction('get_option', [
            'times' => 1,
            'return' => $args
        ]);

        $plugins = $this->versions->pluginVersions();

        $this->assertIsArray($plugins);
        $this->assertGreaterThanOrEqual(1, count($plugins));
    }
}
