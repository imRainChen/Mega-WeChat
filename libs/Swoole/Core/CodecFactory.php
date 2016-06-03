<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/15
 * Time: 11:30
 */

namespace Swoole\Core;


interface CodecFactory
{
    function getEncoder();

    function getDecoder();
}