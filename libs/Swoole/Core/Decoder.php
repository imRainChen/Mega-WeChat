<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/15
 * Time: 11:33
 */

namespace Swoole\Core;


interface Decoder
{
    function decode($message, $fd = null, $server = null);
}