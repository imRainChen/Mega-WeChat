<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/15
 * Time: 11:42
 */

namespace Network;

use Swoole\Command\RequestCommand;

abstract class AbstractRequestCommand implements RequestCommand, EncodeCommand
{
    private $opaque;
    private $fd;

    /**
     * AbstractRequestCommand constructor.
     * @param $opaque int
     * @param $fd int
     */
    public function __construct($opaque, $fd = null)
    {
        $this->opaque = $opaque;
        $this->fd = $fd;
    }

    /**
     * @return mixed
     */
    public function getFd()
    {
        return $this->fd;
    }

    /**
     * @param mixed $fd
     */
    public function setFd($fd)
    {
        $this->fd = $fd;
    }

    public function setOpaque($opaque)
    {
        $this->opaque = $opaque;
    }

    public function getOpaque()
    {
        return $this->opaque;
    }

    public function getRequestHeader()
    {
        return $this;
    }

    public function toString()
    {
        return "opaque:{$this->opaque} fd:{$this->fd}";
    }

}