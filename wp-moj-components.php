<?php
/**
 * Plugin name: WP MoJ Components
 * Plugin URI:  https://github.com/ministryofjustice/wp-moj-components
 * Description: Introduces various functions that are commonly used across the MoJ network of sites
 * Version:     3.1.1
 * Author:      Ministry of Justice
 * Text domain: wp-moj-components
 * Author URI:  https://peoplefinder.service.gov.uk/people/damien-wilson
 * License:     MIT License
 **/

namespace component;

require_once('component/Introduce/Popup.php');
require_once('component/Versions/Plugins.php');
require_once('component/Introduce/PopupSettings.php');
require_once('component/Security/VulnerabilityDB.php');
require_once('component/Security/VulnerabilityDBSettings.php');
require_once('component/Users/Users.php');
require_once('component/Users/UsersSettings.php');

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
