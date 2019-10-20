<?php
/**
 * Created by PhpStorm.
 * User: damienwilson
 * Date: 2019-09-23
 * Time: 11:44
 */


/**
 * @param $class_name
 */
function moj_component_load($class_name)
{
    $class_name = str_replace("\\", "/", $class_name);
    $name_space = strstr($class_name, '/', true);

    if ($name_space === 'component') {
        $path = __DIR__ . "/" . str_replace("\\", "/", $class_name);

        if (file_exists($path . "/" . basename($class_name) . ".php")) {
            require_once($path . "/" . basename($class_name) . ".php");
        }
    }
}

spl_autoload_register('moj_component_load');
