<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/10 0010
 * Time: 18:40
 */

namespace Swoole\Service;

interface Driver
{
    function run($setting);

    function send($client_id, $data);

    function close($client_id);

    //function shutdown();

    function setProtocol($protocol);
}