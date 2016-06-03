<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/11
 * Time: 16:54
 */
namespace Swoole\Queue;

interface Queue
{
    function push($data);

    function pop();
}