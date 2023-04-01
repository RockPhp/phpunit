<?php

class AutoLoad
{

    public static function loadClass($className)
    {
        $fileName = dirname(dirname(__FILE__));
        $fileName .= DIRECTORY_SEPARATOR;
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        if (is_file($fileName)) {
            require_once $fileName;
        }
        $fileName = dirname(dirname(__FILE__));
        $fileName .= DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        if (is_file($fileName)) {
            require_once $fileName;
        }
        $fileName = dirname(dirname(__FILE__));
        $fileName .= DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR;
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        if (is_file($fileName)) {
            require_once $fileName;
        }
    }
}

spl_autoload_register('AutoLoad::loadClass');
if (function_exists('__autoload')) {
    spl_autoload_register('__autoload');
}
