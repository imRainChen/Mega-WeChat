<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/13 0013
 * Time: 16:20
 */
namespace Swoole\Command;

/**
 * 请求命令接口
 *
 * Interface RequestCommand
 * @package Swoole\Command
 */
interface RequestCommand extends CommandHeader
{
    /**
     * 返回请求的头部，用于保存在callBack中
     *
     * @return \Swoole\Command\CommandHeader
     */
    function getRequestHeader();
}