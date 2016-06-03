<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/13 0013
 * Time: 16:17
 */
namespace Swoole\Command;

/**
 * 命令头部
 *
 * Interface CommandHeader
 * @package Swoole\Command
 */
interface CommandHeader extends ICommand {
    /**
     * 协议的自增序列号，用于请求和应答的映射
     * 返回请求的opaque
     *
     * @return integer
     */
    function getOpaque();

}