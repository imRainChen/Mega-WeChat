<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/15
 * Time: 11:47
 */

namespace Network;

/**
 * 设置模板缓存命令
 * Class SetTableCommand
 * @package Network
 */
class SetTableCommand extends AbstractRequestCommand
{
    protected $key;

    /**
     * SetTableCommand constructor.
     * @param int $opaque
     * @param string $key 模板key
     * @param int|null $fd
     */
    public function __construct($opaque, $key, $fd = null)
    {
        parent::__construct($opaque, $fd);
        $this->key = $key;
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
        $cmd = self::SET_TABLE_CMD . self::SPACE . $this->key;
        $cmdLength = strlen($cmd);
        $cmdPack = pack('N1a*', $cmdLength, $cmd);
        $opaquePack = pack('N1', $this->getOpaque());
        $pack = $cmdPack . $opaquePack;
        $packLength = strlen($pack);
        return pack('N1', $packLength) . $pack;
    }

    public function toString()
    {
        return "key:{$this->key} " . parent::toString();
    }
}