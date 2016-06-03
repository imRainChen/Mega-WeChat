<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/5/7
 * Time: 16:30
 */

namespace Swoole\Client;


abstract class Base implements IClient
{
    private $ip;
    private $port;
    private $timeout = 5;
    private $isStart = false;
    private $client;

    public function __construct($socketType)
    {
        $this->client = new \swoole_client($socketType, SWOOLE_SOCK_ASYNC);
        $this->client->on('connect', [$this, 'onConnect']);
        $this->client->on('receive', [$this, 'onReceive']);
        $this->client->on('error', [$this, 'onError']);
        $this->client->on('close', [$this, 'onClose']);
        $this->init();
    }

    public function init()
    {

    }

    public function connect($ip, $port, $timeout)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->timeout = $timeout;

    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return boolean
     */
    public function isStart()
    {
        return $this->isStart;
    }


}