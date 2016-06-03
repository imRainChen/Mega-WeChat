<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/28
 * Time: 11:48
 */

namespace Swoole\Util;


class Config
{
    private static $config = [];

    public static function loadConfig($config = array())
    {
        // $config is file path?
        if (is_string($config)) {
            if (!file_exists($config)) {
                throw new \Exception("[error] profiles [$config] can not be loaded");
            }
            // Load the configuration file into an array
            $config = parse_ini_file($config, true);
        }
        if (is_array($config)) {
            self::$config = array_merge(self::$config, $config);
        }
        return true;
    }

    public static function get($key, $default = null)
    {
        $keys = explode('.', $key);
        if (isset($keys[1])) {
            $result = isset(self::$config[$keys[0]][$keys[1]]) ? self::$config[$keys[0]][$keys[1]] : $default;
        } else {
            $result = isset(self::$config[$key]) ? self::$config[$key] : $default;
        }

        return $result;
    }

    public static function getConfig()
    {
        if (empty(self::$config)) {
            self::loadConfig(dirname(dirname(__DIR__)) . '/Config/main.ini');
        }
        return self::$config;
    }

}