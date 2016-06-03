<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/13 0013
 * Time: 18:46
 */

namespace Swoole\Core;

interface Connection
{
    /**
     * 关闭连接
     *
     */
    function close();

    /**
     * 连接是否有效
     *
     * @return boolean
     */
    function isConnected();

    /**
     * 发送数据
     *
     * @param $data
     * @return mixed
     */
    function send($data);

//    /**
//     * 清除连接的所有属性
//     */
//    function clearAttributes();

//    /**
//     * 获取连接上的某个属性
//     *
//     * @param key
//     * @return mixed
//     */
//    function getAttribute($key);

    /**
     * 获取远端地址
     *
     * @return array
     */
    function getRemoteSocketAddress();

    /**
     * 获取本端IP地址
     *
     * @return array
     */
    function getLocalAddress();

//    /**
//     * 移除属性
//     *
//     * @param key
//     */
//    function removeAttribute($key);
//
//    /**
//     * 设置属性
//     *
//     * @param $key
//     * @param $value
//     */
//    function setAttribute($key, $value);
//
//    /**
//     * 设置属性，如何属性不存在则设置
//     *
//     * @param $key
//     * @param $value
//     * @return mixed
//     */
//    function setAttributeIfAbsent($key, $value);
}