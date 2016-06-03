<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/15
 * Time: 11:47
 */

namespace Network;

/**
 * 发送模板消息命令
 * Class SendCommand
 * @package Network
 */
class SendCommand extends AbstractRequestCommand
{
    protected $openid;
    protected $key;
    protected $data;

    /**
     * SendCommand constructor.
     * @param int $opaque
     * @param string $openid
     * @param string $key
     * @param array|null $data
     * @param int|null $fd
     */
    public function __construct($opaque, $openid, $key, array $data = null, $fd = null)
    {
        parent::__construct($opaque, $fd);
        $this->openid = $openid;
        $this->key = $key;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getOpenId()
    {
        return $this->openid;
    }

    /**
     * @param mixed $openid
     */
    public function setOpenId($openid)
    {
        $this->openid = $openid;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    function encode()
    {
        $body = json_encode($this->data);
        $cmd = self::SEND_CMD . self::SPACE . $this->openid . self::SPACE . $this->key;
        $cmdLength = strlen($cmd);
        $cmdPack = pack('N1a*', $cmdLength, $cmd);
        $opaquePack = pack('N1', $this->getOpaque());
        $bodyLength = strlen($body);
        $bodyPack = $opaquePack . pack('N1a*', $bodyLength, $body);
        //$bodyPack = $opaquePack . $bodyLengthPack . pack('a*', $body);
        $pack = $cmdPack . $bodyPack;
        $packLength = strlen($pack);
        return pack('N1', $packLength) . $pack;
    }

    public function toString()
    {
        return "openid:{$this->openid} key:{$this->key} data:".json_encode($this->data) . ' ' . parent::toString();
    }
}