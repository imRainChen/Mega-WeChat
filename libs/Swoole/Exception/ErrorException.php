<?php
namespace Swoole\Exception;

/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/5/25
 * Time: 12:42
 */
class ErrorException extends \ErrorException
{
    /**
     * Returns if error is one of fatal type.
     *
     * @param array $error error got from error_get_last()
     * @return boolean if error is one of fatal type
     */
    public static function isFatalError($error)
    {
        return isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR]);
    }
}