<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/16
 * Time: 14:53
 */

namespace Network;

use Swoole\Command\RequestCommand;
use Swoole\Core\Decoder;
use Swoole\Exception\ErrorException;
use Swoole\Util\Config;
use Swoole\Util\MessagePacker;

/**
 * 协议是基于文本和二进制混合组成。
 * 通用协议格式：{packLength}{headerLength}command params{opaque}{bodyLength}{body}
 *      packLength：整个包长
 *      headerLength：头部长度，由command和params组成的长度
 *      command：协议命令
 *      params：参数列表，每个参数用“ ”隔开
 *      opaque：协议自增序号
 *      bodyLength：协议体长度
 *      body：协议体
 *
 *
 * Class WechatDecoder
 * @package Network
 */
class MegaWechatDecoder implements Decoder
{
    // 默认最大包长度2M
    private $packageMaxLength = 2465792;

    public function __construct()
    {
        if (($packageMaxLength = Config::get('setting.package_max_length')) != null ) {
            $this->packageMaxLength = $packageMaxLength;
        }
    }

    /**
     * @param $message
     * @param $server
     * @return RequestCommand
     * @throws \Exception
     */
    public function decode($message, $fd = null, $server = null)
    {
        if (empty($message)) {
            return null;
        }

        try {
            $packer = new MessagePacker($message);
            $packLength = $packer->readInt();
            // 长度错误
            if (strlen($message) > $this->packageMaxLength || $packLength > $this->packageMaxLength)
            {
                throw new CommandException('Package too big');
            }
            $header = $packer->readString();
            $cmd = explode(EncodeCommand::SPACE, $header);
            $op = substr($cmd[0], 0, 1);
            switch ($op)
            {
                case 's' :
                    return $this->decodeSend($packer, $cmd, $fd);
                case 'r' :
                    return $this->decodeBoolean($packer, $cmd);
                case 't' :
                    return $this->decodeSetTable($packer, $cmd, $fd);
                case 'p' :
                    return $this->decoderPush($packer, $cmd);
                default :
                    throw new CommandException('Unknown command');
            }
        } catch (ErrorException $ex) {
            throw new CommandException('Unknown command');
        }
    }

    /**
     * 解码send命令数据
     *
     * send {openid} {key}{opaque}{length}{message}
     *
     * @param $messagePacker MessagePacker
     * @param $cmd array
     * @param $fd int
     * @return SendCommand
     * @throws CommandException
     */
    private function decodeSend($messagePacker, $cmd, $fd)
    {
        $this->assertCommand($cmd[0], EncodeCommand::SEND_CMD);
        $opaque = $messagePacker->readInt();
        $data = json_decode($messagePacker->readString(), true);
        return new SendCommand($opaque, $cmd[1], $cmd[2], $data, $fd);
    }

    /**
     * 解码push命令数据
     *
     * push {openid} {key}{opaque}{length}{message}
     *
     * @param $messagePacker MessagePacker
     * @param $cmd array
     * @return PushCommand
     * @throws CommandException
     */
    private function decoderPush($messagePacker, $cmd)
    {
        $this->assertCommand($cmd[0], EncodeCommand::PUSH_CMD);
        $opaque = $messagePacker->readInt();
        $data = json_decode($messagePacker->readString(), true);
        return new PushCommand($opaque, $cmd[1], $cmd[2], $data);
    }

    /**
     *
     * result {code}{opaque}{length}{message}
     *
     * @param $messagePacker MessagePacker
     * @param $cmd array
     * @return BooleanCommand
     * @throws CommandException
     */
    private function decodeBoolean($messagePacker, $cmd)
    {
        $this->assertCommand($cmd[0], EncodeCommand::RESULT_CMD);
        $opaque = $messagePacker->readInt();
        $messageLen = $messagePacker->readInt();
        if ($messageLen === 0) {
            return new BooleanCommand($cmd[1], null, $opaque);
        } else {
            $message = $messagePacker->readString($messageLen);
            return new BooleanCommand($cmd[1], $message, $opaque);
        }
    }

    /**
     * 解码tset命令数据
     *
     * tset {key}{opaque}
     *
     * @param $messagePacker MessagePacker
     * @param $cmd array
     * @param $fd int
     * @return SetTableCommand
     * @throws CommandException
     */
    private function decodeSetTable($messagePacker, $cmd, $fd)
    {
        $this->assertCommand($cmd[0], EncodeCommand::SET_TABLE_CMD);
        $opaque = $messagePacker->readInt();
        return new SetTableCommand($opaque, $cmd[1], $fd);
    }

    private function assertCommand($cmd, $expect) {
        if ($cmd !== $expect) {
            throw new CommandException("Expect {$expect} but was {$cmd}");
        }
    }
}