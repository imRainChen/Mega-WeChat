<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/15
 * Time: 11:32
 */

namespace Swoole\Core;


interface Encoder
{
    /**
     * 编码
     * @return string
     */
    function encode($message);
}