<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/28
 * Time: 10:55
 */

namespace Swoole\Util;


use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class Log
{
    const DEFAULT_LOG_NAME = 'swoole';

    private static $logHandlers;

    /**
     * Add log handlers to tailor logging for your use case. Default logging
     * is the Monolog default, a Monolog StreamHandler('php://stderr', static::DEBUG)
     * Use Monolog NullHandler to disable all logging.
     * @param $handlers
     */
    public static function setLogHandlers($handlers)
    {
        self::$logHandlers = $handlers;
    }

    /**
     * Get the current log handler array
     * @return mixed
     */
    public static function getLogHandlers()
    {
        return self::$logHandlers;
    }

    /**
     * Returns the logger for standard logging in the library
     * @return Logger
     */
    public static function getLogger()
    {
        if (!self::$logHandlers) {
            $logPath = Config::get('log.log_file') ?: dirname(dirname(dirname(__DIR__))) . '/logs/swoole.log';
            $logPrefix = Config::get('log.log_prefix') ?: self::DEFAULT_LOG_NAME ;
            $logHandler = new RotatingFileHandler($logPath, 0, self::getLevel(Config::get('log.log_level')));
            return new Logger($logPrefix, [$logHandler]);
        }
        return new Logger(self::DEFAULT_LOG_NAME, self::$logHandlers);
    }

    public static function getLevel($level)
    {
        $level = strtoupper(trim($level));
        switch ($level) {
            case 'DEBUG' :
                $level = \Monolog\Logger::DEBUG;
                break;
            case 'INFO' :
                $level = \Monolog\Logger::INFO;
                break;
            case 'NOTICE' :
                $level = \Monolog\Logger::NOTICE;
                break;
            case 'WARNING' :
                $level = \Monolog\Logger::WARNING;
                break;
            case 'ERROR' :
                $level = \Monolog\Logger::ERROR;
                break;
            case 'CRITICAL' :
                $level = \Monolog\Logger::CRITICAL;
                break;
            case 'ALERT' :
                $level = \Monolog\Logger::ALERT;
                break;
            case 'EMERGENCY' :
                $level = \Monolog\Logger::EMERGENCY;
                break;
        }
        return $level;
    }

}