<?php
/**
 * Plugin name: WP MoJ Components
 * Plugin URI:  https://github.com/ministryofjustice/wp-moj-components
 * Description: Introduces various functions that are commonly used across the MoJ network of sites
 * Version:     2.0.0
 * Author:      Ministry of Justice
 * Author URI:  https://peoplefinder.service.gov.uk/people/damien-wilson
 * License:     MIT License
 **/

namespace component;

include_once "load.php";

new Helper();

/**********************/

new Versions();
new Introduce();


