<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/10 0010
 * Time: 19:48
 */
namespace Swoole\Protocol;

interface IProtocol
{
    function onStart(\swoole_server $server, $workerId);

    function onConnect(\swoole_server $server, $fd, $from_id);

    /**
     * @param $server \swoole_server
     * @param $client_id
     * @param $from_id
     * @param $data
     * @return mixed
     */
    function onReceive(\swoole_server $server,$fd, $from_id, $data);

    function onClose(\swoole_server $server, $fd, $from_id);

    function onShutdown(\swoole_server $server, $workerId);

    function onTask(\swoole_server $server, $taskId, $fromId, $data);

    function onFinish(\swoole_server $server, $taskId, $data);

    function onTimer(\swoole_server $server, $interval);

    function onRequest($request, $response);
}