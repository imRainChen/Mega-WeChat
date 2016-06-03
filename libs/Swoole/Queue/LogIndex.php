<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/13
 * Time: 14:47
 */

namespace Swoole\Queue;

use SplFileInfo;
use SplFileObject;
use Swoole\Util\ByteBuffer;
use Swoole\Util\MessagePacker;
use swoole_buffer;
use swoole_atomic;

/**
 * 文件索引
 * Class LogIndex
 * @package Swoole\Queue
 */
class LogIndex
{
    const INDEX_FILE_LIMIT_LENGTH = 20;

    private $file;
    /** @var swoole_buffer  */
    public $buffer;

    /*
     * 文件操作位置信息
     */
    private $readerPosition = -1;
    private $writerPosition = -1;
    private $readerIndex = -1;
    private $writerIndex = -1;
    private $size = 0;

    /**
     * LogIndex constructor.
     * @param $path
     * @throws FileFormatException
     */
    public function __construct($path)
    {
        $file = new SplFileInfo($path);
        $log = null;
        if ($file->isFile() === false){
            $file->setFileClass('SplFileObject');
            /** @var SplFileObject $splFile */
            $this->file = $file->openFile('w+');
            $log = ByteBuffer::allocate(self::INDEX_FILE_LIMIT_LENGTH);
            $log->writeInt(LogEntity::MESSAGE_START_POSITION);   // 0 readerPos
            $log->writeInt(LogEntity::MESSAGE_START_POSITION);   // 4 writerPos
            $log->writeInt(1);                                   // 8 readerIndex
            $log->writeInt(1);                                   // 12 writerIndex
            $log->writeInt(0);                                   // 16 size
            $this->file->fwrite($log->getData());
            $this->file->fflush();
            $this->readerPosition = LogEntity::MESSAGE_START_POSITION;
            $this->writerPosition = LogEntity::MESSAGE_START_POSITION;
            $this->readerIndex = 1;
            $this->writerIndex = 1;
        } else {
            $file->setFileClass('SplFileObject');
            /** @var SplFileObject $splFile */
            $this->file = $file->openFile('r+');
            if ($this->file->getSize() < self::INDEX_FILE_LIMIT_LENGTH) {
                throw new FileFormatException('file format error!');
            }
            $data = $this->file->fread(self::INDEX_FILE_LIMIT_LENGTH);
            $log = ByteBuffer::allocate(self::INDEX_FILE_LIMIT_LENGTH);
            $log->resetData($data);
            $this->readerPosition = $log->readInt();
            $this->writerPosition = $log->readInt();
            $this->readerIndex = $log->readInt();
            $this->writerIndex = $log->readInt();
            $this->size = $log->readInt();
        }
//        $this->buffer = new swoole_buffer(self::INDEX_FILE_LIMIT_LENGTH);
//        $this->buffer->write(0, $log->getData());
        $this->buffer = $log;
    }

    public function putReaderPosition($pos)
    {
        //$bin = pack("N1", $pos);
        //$this->buffer->write(0, $bin);
        $this->buffer->position(0);
        $this->buffer->writeInt($pos);
        $this->readerPosition = $pos;
    }

    public function putWriterPosition($pos)
    {
//        $bin = pack("N1", $pos);
//        $this->buffer->write(4, $bin);
        $this->buffer->position(4);
        $this->buffer->writeInt($pos);
        $this->writerPosition = $pos;
    }

    public function putReaderIndex($pos)
    {
//        $bin = pack("N1", $pos);
//        $this->buffer->write(8, $bin);
        $this->buffer->position(8);
        $this->buffer->writeInt($pos);
        $this->readerIndex = $pos;
    }

    public function putWriterIndex($pos)
    {
//        $bin = pack("N1", $pos);
//        $this->buffer->write(12, $bin);
        $this->buffer->position(12);
        $this->buffer->writeInt($pos);
        $this->writerIndex = $pos;
    }

    public function increaseSize()
    {
        $this->size++;
//        $bin = pack("N1", $this->size);
//        $this->buffer->write(16, $bin);
        $this->buffer->position(16);
        $this->buffer->writeInt($this->size);
    }

    public function decreaseSize()
    {
        $this->size--;
//        $bin = pack("N1", $this->size);
//        $this->buffer->write(16, $bin);
        $this->buffer->position(16);
        $this->buffer->writeInt($this->size);
    }

    /**
     * @return int
     */
    public function getReaderPosition()
    {
        return $this->readerPosition;
    }

    /**
     * @return int
     */
    public function getWriterPosition()
    {
        return $this->writerPosition;
    }

    /**
     * @return int
     */
    public function getReaderIndex()
    {
        return $this->readerIndex;
    }

    /**
     * @return int
     */
    public function getWriterIndex()
    {
        return $this->writerIndex;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    public function headerInfo()
    {
        return "readerPosition:{$this->readerPosition} writerPosition:{$this->writerPosition} readerIndex:{$this->readerIndex} writerIndex:{$this->writerIndex} size:{$this->getSize()}";
    }

    public function force()
    {
//        $data = $this->buffer->read(0, self::INDEX_FILE_LIMIT_LENGTH);
        $data = $this->buffer->getData();
        if ($data === false) {
            throw new FileBufferException('buffer occur error!');
        }
        $this->file->fseek(0);
        $this->file->fwrite($data);
        $this->file->fflush();
    }

    public function close()
    {
        $this->force();
        $this->buffer->clear();
        unset($this->buffer);
        unset($this->file);
    }
}

////测试
//include "/qcloud/www/autoload.php";
//
//$log = new LogIndex('/qcloud/logs/index.log');
//echo $log->headerInfo() . PHP_EOL;
//$log->increaseSize();
//$log->force();
//$log = new LogIndex('/qcloud/logs/index.log');
//echo $log->headerInfo() . PHP_EOL;

