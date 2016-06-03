<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/13 0013
 * Time: 17:52
 */
namespace Swoole\Command;

interface CommandFactory {
    /**
     * 创建特定于协议的BooleanAckCommand
     *
     * @param \Swoole\Command\CommandHeader $request 请求头
     * @param \Swoole\Command\ResponseStatus $responseStatus 响应状态
     * @param string $errorMsg 错误信息
     * @return \Swoole\Command\BooleanAckCommand
     */
    public function createBooleanAckCommand(CommandHeader $request, ResponseStatus $responseStatus, $errorMsg);
}