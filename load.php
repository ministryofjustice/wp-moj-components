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
        $path_sub = $path . "/sub/";

        if (is_dir($path_sub)) {
            if ($dh = opendir($path_sub)) {
                while (($file = readdir($dh)) !== false) {
                    if (filetype($path_sub . $file) !== 'dir') {
                        if (strpos($file, '.php') > 0) {
                            require_once($path_sub . $file . "");
                        }
                    }
                }
                closedir($dh);
            }
        }

        require_once($path . "/" . basename($class_name) . ".php");
    }
}

spl_autoload_register('moj_component_load');
