<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/13 0013
 * Time: 20:33
 */
namespace Swoole\Core\Implement;

use Swoole\Core\Connection;
use InvalidArgumentException;

abstract class BaseConnection implements Connection
{
    private $socketType;

    /**
     * 连接的地址
     * @var array('host' => '127.0.0.1', 'port' => 53652)
     */
    private $remoteAddress = [];

    public function __construct($config)
    {
        if (!is_array($config)) {
            throw new InvalidArgumentException("Invalid config");
        }

        if (array_key_exists('socket_type', $config)) {
            throw new InvalidArgumentException('socket_type must be set.');
        }

        $this->setSocketType($config['socket_type']);
    }

    public function getSocketType()
    {
        return $this->socketType;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getRemoteSocketAddress()
    {
        if (empty($this->remoteAddress)) {
            $address = $this->getRealRemoteSocketAddress();
            if(is_array($address)) {
                $this->remoteAddress = $address;
            }
        }
        return $this->remoteAddress;
    }

    /**
     * 获取远端地址的真正实现
     * @return array
     */
    abstract public function getRealRemoteSocketAddress();

    /**
     * 设置socket的类型
     * @param $socketType
     */
    public function setSocketType($socketType)
    {
        if (!(($socketType | $this->validSocketOps()) == $this->validSocketOps()))
            throw new \InvalidArgumentException('invalid socket type');

        $this->socketType = $socketType;
    }

    /**
     * 所有有效的SocketType
     * @return int
     */
    public static function validSocketOps()
    {
        return (SWOOLE_SOCK_TCP
            | SWOOLE_SOCK_TCP6
            | SWOOLE_SOCK_UDP
            | SWOOLE_SOCK_UDP6
            | SWOOLE_SOCK_SYNC
            | SWOOLE_SOCK_ASYNC
        );
    }
}