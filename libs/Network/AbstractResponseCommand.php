<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/27
 * Time: 11:28
 */

namespace Network;


use Swoole\Command\ResponseCommand;

abstract class AbstractResponseCommand implements ResponseCommand, EncodeCommand
{
    private $opaque;
    private $responseStatus;
    private $responseTime;
    private $responseHost;

    /**
     * AbstractResponseCommand constructor.
     * @param $opaque
     */
    public function __construct($opaque)
    {
        $this->opaque = $opaque;
    }

    /**
     * @inheritDoc
     */
    function getOpaque()
    {
        return $this->opaque;
    }

    /**
     * @inheritDoc
     */
    function getResponseStatus()
    {
        return $this->responseStatus;
    }

    /**
     * @inheritDoc
     */
    function setResponseStatus($responseStatus)
    {
        $this->responseStatus = $responseStatus;
    }

    /**
     * @inheritDoc
     */
    function getResponseHost()
    {
        return $this->responseHost;
    }

    /**
     * @inheritDoc
     */
    function setResponseHost($address)
    {
        $this->responseHost = $address;
    }

    /**
     * @inheritDoc
     */
    function getResponseTime()
    {
        return $this->responseTime;
    }

    /**
     * @inheritDoc
     */
    function setResponseTime($time)
    {
        $this->responseTime = $time;
    }

    /**
     * @inheritDoc
     */
    function setOpaque($opaque)
    {
        $this->opaque = $opaque;
    }

}