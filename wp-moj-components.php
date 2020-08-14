<?php

/**
 *
 * The file responsible for starting the MoJ Components plugin
 *
 * This plugin supports common functions needed throughout the JOTW site portfolio.
 *
 * @package wp-moj-components
 *
 * Plugin name: WP MoJ Components
 * Plugin URI:  https://github.com/ministryofjustice/wp-moj-components
 * Description: Introduces functions that are commonly used across the MoJ network of sites
 * Version:     3.3.1
 * Author:      Ministry of Justice
 * Text domain: wp-moj-components
 * Author URI:  https://ministryofjustice.github.io/justice-on-the-web/#justice-on-the-web
 * License:     MIT License
 * License URI: https://opensource.org/licenses/MIT
 * Copyright:   Crown Copyright (c) Ministry of Justice
 **/

namespace component;

defined('ABSPATH') || exit;

require_once('component/Introduce/Popup.php');
require_once('component/Versions/Plugins.php');
require_once('component/Introduce/PopupSettings.php');
require_once('component/Security/VulnerabilityDB.php');
require_once('component/Security/VulnerabilityDBSettings.php');
require_once('component/Users/Users.php');
require_once('component/Users/UsersSettings.php');
require_once('component/Users/UserSwitch.php');
require_once('component/Sitemap/Sitemap.php');
require_once('component/Sitemap/SitemapSettings.php');
require_once('component/Analytics/Analytics.php');
require_once('component/Analytics/AnalyticsSettings.php');

include_once "load.php";

define('MOJ_COMPONENT_PLUGIN_PATH', __FILE__);

global $mojHelper;
$mojHelper = new Helper();

new AdminSettings();

/**********************/

new Versions();
new Introduce();
new Security();
new Users();
new Sitemap();
new Analytics();
