<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/13 0013
 * Time: 21:48
 */
namespace Swoole\Core\Implement;

use Swoole\Core\Connection;

/**
 * 客户端连接
 *
 * Class ClientConnection
 * @package Swoole\Core\Implement
 */
class ClientConnection extends BaseConnection implements Connection
{
    /** @var \swoole_server $server */
    private $server;
    private $fd;
    // reactor线程id
    private $fromId = null;

    /**
     * @inheritDoc
     */
    function close($reset = false)
    {
        return $this->server->close($this->fd, $reset);
    }

    /**
     * @inheritDoc
     */
    function isConnected()
    {
        return $this->server->exist($this->fd);
    }

    /**
     * @inheritDoc
     */
    function send($data)
    {
        return $this->server->send($this->fd, $data, $this->fromId);
    }

    /**
     * @inheritDoc
     */
    public function getRealRemoteSocketAddress()
    {
        $connectionInfo = $this->server->connection_info($this->fd, $this->fromId);
        if ($connectionInfo === false) {
            throw new \ConnectionException('Connection does not exist or has been closed');
        }

        return [
            'host' => $connectionInfo['remote_ip'],
            'port' => $connectionInfo['remote_port'],
        ];
    }

    /**
     * @inheritDoc
     */
    function getLocalAddress()
    {
        return swoole_get_local_ip();
    }

    function getFd()
    {
        return $this->fd;
    }

    function setFd($fd)
    {
        $this->fd = $fd;
    }

}