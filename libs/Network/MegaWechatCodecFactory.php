<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/17
 * Time: 10:18
 */

namespace Network;


use Swoole\Core\CodecFactory;

class MegaWechatCodecFactory implements CodecFactory
{
    private static $encoder;
    private static $decoder;

    /**
     * WechatCodecFactory constructor.
     */
    public function __construct()
    {
        self::$encoder = new MegaWechatEncoder();
        self::$decoder = new MegaWechatDecoder();
    }

    /**
     * @return MegaWechatEncoder
     */
    function getEncoder()
    {
        return self::$encoder;
    }

    /**
     * @return MegaWechatDecoder
     */
    function getDecoder()
    {
        return self::$decoder;
    }

}