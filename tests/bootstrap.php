<?php

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

WP_Mock::setUsePatchwork(true);
WP_Mock::bootstrap();

require_once dirname(dirname(__FILE__)) . '/wp-moj-components.php';
