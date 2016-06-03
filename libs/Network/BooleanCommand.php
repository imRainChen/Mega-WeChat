<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/27
 * Time: 12:04
 */

namespace Network;


use Swoole\Command\BooleanAckCommand;
use Swoole\Command\ResponseStatus;

class BooleanCommand extends AbstractResponseCommand implements BooleanAckCommand
{
    private $code;
    private $message;

    /**
     * BooleanCommand constructor.
     * @param int $code
     * @param string $message
     * @param int $opaque
     */
    public function __construct($code, $message, $opaque)
    {
        parent::__construct($opaque);
        $this->code = (int) $code;
        $this->message = $message;
        switch ($code) {
            case HttpStatus::SUCCESS;
            case HttpStatus::GO_ON;
                $this->setResponseStatus(ResponseStatus::NO_ERROR);
                break;
            default:
                $this->setResponseStatus(ResponseStatus::ERROR);
        }
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritDoc
     */
    public function getErrorMsg()
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function setErrorMsg($errorMsg)
    {
        $this->message = $errorMsg;
    }

    function encode()
    {
        $cmd = self::RESULT_CMD . self::SPACE . $this->code;
        $cmdLength = strlen($cmd);
        $cmdPack = pack('N1a*', $cmdLength, $cmd);
        $opaquePack = pack('N1', $this->getOpaque());
        $messageLength = strlen($this->message);
        $bodyPack = $opaquePack . pack('N1a*', $messageLength, $this->message);
        $pack = $cmdPack . $bodyPack;
        $packLength = strlen($pack);
        return pack('N1', $packLength) . $pack;
    }

    /**
     * @inheritDoc
     */
    function isBoolean()
    {
        return true;
    }

}