<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/10 0010
 * Time: 20:22
 */
namespace Swoole\Protocol;
use Swoole\Core\CodecFactory;
use Swoole\Core\Decoder;
use Swoole\Core\Encoder;

/**
 * 协议基类，实现一些公用的方法
 * @package Swoole\Protocol
 */
abstract class Base implements IProtocol
{
    public $server;

    /** @var CodecFactory */
    protected $codecFactory;
    /** @var Encoder */
    private $encoder;
    /** @var Decoder */
    private $decoder;

    function __construct()
    {
        $this->init();
    }

    public function init()
    {

    }

    /**
     * 打印Log信息
     * @param $msg
     */
    public function log($msg)
    {
        $log = "[" . date("Y-m-d G:i:s") . " " . floor(microtime() * 1000) . "]" . $msg;
        echo $log;
    }

    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     * @return CodecFactory
     */
    public function getCodecFactory()
    {
        return $this->codecFactory;
    }

    /**
     * @param CodecFactory $codecFactory
     */
    public function setCodecFactory($codecFactory)
    {
        $this->codecFactory = $codecFactory;
        $this->encoder = $codecFactory->getEncoder();
        $this->decoder = $codecFactory->getDecoder();
    }

    public function decode($message, $fd = null, $server = null)
    {
        return $this->decoder->decode($message, $fd, $server);
    }

    public function encode($message)
    {
        return $this->encoder->encode($message);
    }

}