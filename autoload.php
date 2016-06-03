<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/16
 * Time: 17:05
 */
defined('APP_PATH') or define("APP_PATH", __DIR__);
defined('APP_DEBUG') or define('APP_DEBUG', true);

class Autoload
{
    public static $classMap = [];

    public static function getLoader($className)
    {
        if (isset(static::$classMap[$className])) {
            $classFile = static::$classMap[$className];
        } elseif ($pos = strpos($className, '\\') !== false) {
            $classFile = APP_PATH . '/libs/'  . str_replace('\\', '/', $className) . '.php';
            if (!is_file($classFile)) {
                return;
            }
        } else {
            return;
        }

        include($classFile);

        if (APP_DEBUG && !class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
            throw new \Exception("Unable to find '$className' in file: $classFile. Namespace missing?");
        }
    }
}

require(__DIR__ . '/vendor/autoload.php');

spl_autoload_register(['Autoload', 'getLoader'], true, true);
Autoload::$classMap = require(__DIR__ . '/libs/Swoole/classes.php');