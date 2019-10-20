<?php
/**
 * Plugin name: WP MoJ Components
 * Plugin URI:  https://github.com/ministryofjustice/wp-moj-components
 * Description: Introduces various functions that are commonly used across the MoJ network of sites
 * Version:     2.1.0
 * Author:      Ministry of Justice
 * Text domain: wp-moj-components
 * Author URI:  https://peoplefinder.service.gov.uk/people/damien-wilson
 * License:     MIT License
 **/

namespace component;

include_once "load.php";

require_once('component/Introduce/Popup.php');

global $mojHelper;
$mojHelper = new Helper();

new AdminSettings();

/**********************/

new Versions();
new Introduce();
new Security();
