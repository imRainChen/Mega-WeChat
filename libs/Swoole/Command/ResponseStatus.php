<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/13 0013
 * Time: 16:32
 */
namespace Swoole\Command;

/**
 *
 * 应答状态
 *
 * Class ResponseStatus
 * @package Swoole\Command
 */
class ResponseStatus
{
    const NO_ERROR = 0;  // 正常成功
    const ERROR = 1; // 错误，响应端主动设置
    const EXCEPTION = 2; // 异常
    const UNKNOWN = 3; // 没有注册Listener，包括CheckMessageListener和MessageListener
    const ERROR_COMM  = 4; // 通讯错误，如编码错误
    const NO_PROCESSOR = 5;// 没有该请求命令的处理器
    const TIMEOUT = 6; // 响应超时

    private static $errorMessage = [
        0 => 'success',
        1 => 'Error by user',
        2 => 'Exception occured',
        3 => 'Unknow error',
        4 => 'Thread pool is busy',
        5 => 'Communication error',
        6 => 'There is no processor to handle this request',
        7 => 'Operation timeout'
    ];

    public static function getErrorMessage($status)
    {
        if (!isset(self::$errorMessage[$status]))
            throw new \InvalidArgumentException('Must be ResponseStatus Class const');

        return self::$errorMessage[$status];
    }
}

