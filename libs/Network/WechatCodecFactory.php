<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/17
 * Time: 10:18
 */

namespace Network;


use Swoole\Core\CodecFactory;

class WechatCodecFactory implements CodecFactory
{
    private static $encoder;
    private static $decoder;

    /**
     * WechatCodecFactory constructor.
     */
    public function __construct()
    {
        self::$encoder = new WechatEncoder();
        self::$decoder = new WechatDecoder();
    }

    /**
     * @return WechatEncoder
     */
    function getEncoder()
    {
        return self::$encoder;
    }

    /**
     * @return WechatDecoder
     */
    function getDecoder()
    {
        return self::$decoder;
    }

}