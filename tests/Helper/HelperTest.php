<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-10-01
 * Time: 13:07
 */

namespace MOJComponents\Helper;

use WP_Mock;

class HelperTest extends WP_Mock\Tools\TestCase
{
    public $helper;

    public function setUp() : void
    {
        \WP_Mock::setUp();
        \WP_Mock::passthruFunction('esc_url');
        $this->helper = new Helper();
    }

    public function tearDown() : void
    {
        \WP_Mock::tearDown();
        $this->helper = null;
    }

    public function testActions()
    {
        parent::assertActionsCalled();
    }

    public function testGetPageUrl()
    {
        global $wp;
        $wp = new \stdClass();
        $wp->request = '/a-page/on-my/site';

        \WP_Mock::userFunction('home_url', [
            'times' => 1,
            'args' => $wp->request,
            'return' => 'https://example.com' . $wp->request
        ]);

        $result = $this->helper->getPageUrl();

        $this->assertEquals('https://example.com/a-page/on-my/site', $result);
    }

    public function testGetMorningTimePeriod()
    {
        $time = $this->helper->getTimePeriod(1570096515); // 09.55 in the morning
        $this->assertEquals('morning', $time);
    }

    public function testGetAfternoonTimePeriod()
    {
        $time = $this->helper->getTimePeriod(1570107315); // 12:55 in the afternoon

        $this->assertEquals('afternoon', $time);
    }

    public function testGetEveningTimePeriod()
    {
        $time = $this->helper->getTimePeriod(1570132515); // 19:55 in the evening

        $this->assertEquals('evening', $time);
    }

    public function testNotGetEveningTimePeriod()
    {
        $time = $this->helper->getTimePeriod(1570107315); // 12:55 in the afternoon

        $this->assertEquals('afternoon', $time);
        $this->assertNotEquals('evening', $time);
    }

    public function testAssetPaths()
    {
        $path = str_replace('/tests/Helper', '', __DIR__);

        \WP_Mock::userFunction('plugins_url', [
            'times' => 4,
            'args' => ['assets/', $path],
            'return' => $path . '/assets/'
        ]);

        $result = $this->helper->cssPath($path);
        $this->assertEquals($path . '/assets/css/', $result);

        $result = $this->helper->fontPath($path);
        $this->assertEquals($path . '/assets/fonts/', $result);

        $result = $this->helper->imagePath($path);
        $this->assertEquals($path . '/assets/images/', $result);

        $result = $this->helper->jsPath($path);
        $this->assertEquals($path . '/assets/js/', $result);
    }
}
