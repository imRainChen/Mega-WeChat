<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/13 0013
 * Time: 16:22
 */
namespace Swoole\Command;

/**
 * 响应命令接口
 *
 * Interface ResponseCommand
 * @package Swoole\Command
 */
interface ResponseCommand extends CommandHeader {

    /**
     * 返回响应状态
     *
     * @return ResponseStatus
     */
    function getResponseStatus();

    /**
     * 设置响应状态
     *
     * @param int
     */
    function setResponseStatus($responseStatus);

    /**
     * 是否为BooleanAckCommand
     *
     * @return bool
     */
    function isBoolean();


    /**
     * 返回响应的远端地址
     *
     * @return string
     */
    function getResponseHost();


    /**
     * 设置响应的远端地址
     *
     * @param string $address
     */
    function setResponseHost($address);


    /**
     * 返回响应的时间戳
     *
     * @return int
     */
    function getResponseTime();


    /**
     * 设置响应时间戳
     *
     * @param int $time
     */
    function setResponseTime($time);


    /**
     * 设置响应的opaque
     *
     * @param int $opaque
     */
    function setOpaque($opaque);
}