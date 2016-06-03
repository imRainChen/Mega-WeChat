<?php

namespace Swoole\Client;

/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/5/7
 * Time: 16:28
 */
interface IClient
{
    public function onConnect(\swoole_client $client);

    public function onReceive(\swoole_client $client, $data);

    public function onError(\swoole_client $client);

    public function onClose(\swoole_client $client);

}