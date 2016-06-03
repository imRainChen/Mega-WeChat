<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/13 0013
 * Time: 17:54
 */
namespace Swoole\Command;

/**
 * 响应消息，返回应答成功或者失败，仅是标记接口
 *
 * Interface BooleanAckCommand
 * @package Swoole\Command
 */
interface BooleanAckCommand extends ResponseCommand
{
    /**
     * 获取附加错误信息
     *
     * @return string
     */
    public function getErrorMsg();

    /**
     * 设置附加错误信息
     *
     * @param string $errorMsg
     */
    public function setErrorMsg($errorMsg);
}