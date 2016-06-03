<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/17
 * Time: 10:27
 */

namespace Network;


use Swoole\Core\Encoder;

class WechatEncoder implements Encoder
{
    /**
     * @param EncodeCommand $message
     * @param $server
     */
    function encode($message)
    {
        return $message->encode();
    }

}