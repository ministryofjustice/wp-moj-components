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
 * Version:     3.5.2
 * Author:      Ministry of Justice
 * Text domain: wp-moj-components
 * Author URI:  https://ministryofjustice.github.io/justice-on-the-web/#justice-on-the-web
 * License:     MIT License
 * License URI: https://opensource.org/licenses/MIT
 * Copyright:   Crown Copyright (c) Ministry of Justice
 **/

namespace MOJComponents;

defined('ABSPATH') || exit;

use MOJComponents\Helper\Helper;
use MOJComponents\AdminSettings\AdminSettings;
use MOJComponents\Introduce\Introduce;
use MOJComponents\Versions\Versions;
use MOJComponents\Security\Security;
use MOJComponents\Users\Users;
use MOJComponents\Sitemap\Sitemap;
use MOJComponents\Analytics\Analytics;
use MOJComponents\Multisite\Multisite;
use MOJComponents\Head\Head;

define('MOJ_COMPONENT_PLUGIN_PATH', __FILE__);

global $mojHelper;
$mojHelper = new Helper();

new AdminSettings();

/*********
 * Load Components
 ******************/

new Versions();
new Introduce();
new Security();
new Users();
new Sitemap();
new Analytics();
new Multisite();
new Head();
