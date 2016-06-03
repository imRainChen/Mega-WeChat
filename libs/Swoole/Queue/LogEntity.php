<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/11
 * Time: 16:58
 */
namespace Swoole\Queue;

use SplFileObject;
use SplFileInfo;
use Swoole\Util\ByteBuffer;

/**
 * 文件队列实例
 * Class LogEntity
 * @package Swoole\Queue
 */
class LogEntity
{
    const MESSAGE_START_POSITION = 8;
    const WRITE_SUCCESS = 1;
    const WRITE_FAILURE = 2;
    const WRITE_FULL = 3;

    private $file;
    private $fileLimitLength = 1024 * 1024 * 40;
    public $buffer;
    /** @var LogIndex 队列索引 */
    private $logIndex;
    // 定时器ID
    private $timer;
    /** @var FileWorker 删除和预创建文件worker */
    private $fileWorker;

    /*
     * 文件操作位置信息
     */
    private $readerPosition = -1;
    private $writerPosition = -1;
    private $nextFile = -1;
    private $endPosition = -1;
    private $currentFileNumber = -1;

    public function __construct($path, LogIndex $logIndex, $fileNumber, $fileLimitLength = 1024 * 1024 * 40, $fileWorker)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('path must be string');
        }

        if (!$logIndex instanceof LogIndex) {
            throw new \InvalidArgumentException('logIndex must be LogIndex class');
        }

        if (!is_int($fileNumber)) {
            throw new \InvalidArgumentException('fileNumber must be int');
        }

        if (!is_int($fileLimitLength)) {
            throw new \InvalidArgumentException('fileLimitLength must be int');
        }

        $file = new SplFileInfo($path);
        $this->logIndex = $logIndex;
        $this->currentFileNumber = $fileNumber;
        $this->fileWorker = $fileWorker;
        $this->fileLimitLength = $fileLimitLength;
        if ($file->isFile() === false){
            $file->setFileClass('SplFileObject');
            $this->file = $file->openFile('w+');
            $this->create();
            $this->fileWorker->addCreateFile($fileNumber + 1);
        } else {
            $file->setFileClass('SplFileObject');
            /** @var SplFileObject $splFile */
            $this->file = $file->openFile('r+');
            $data = $this->file->fread($this->fileLimitLength);
            if (strlen($data) < self::MESSAGE_START_POSITION) {
                throw new FileFormatException('file format error!');
            }
            $this->buffer = ByteBuffer::allocate($this->fileLimitLength);
            $this->buffer->resetData($data);
            $this->nextFile = $this->buffer->readInt();
            $this->endPosition = $this->buffer->readInt();
            if ($this->endPosition === -1) {
                $this->writerPosition = $this->logIndex->getWriterPosition();
            } else if ($this->endPosition === -2) {
                $this->writerPosition = LogEntity::MESSAGE_START_POSITION;
                $this->logIndex->putWriterPosition($this->writerPosition);
                $this->buffer->position(4);
                $this->buffer->writeInt(-1);
                $this->endPosition = -1;
            } else {
                $this->writerPosition = $this->endPosition;
            }

            if ($this->logIndex->getReaderIndex() == $this->currentFileNumber) {
                $this->readerPosition = $this->logIndex->getReaderPosition();
            } else {
                $this->readerPosition = LogEntity::MESSAGE_START_POSITION;
            }
        }

        // 每10秒刷入硬盘
        $this->timer = swoole_timer_tick(10000, function() {
            $this->force();
        });
    }

    private function create()
    {
        $this->buffer = ByteBuffer::allocate($this->fileLimitLength);
        $this->buffer->writeInt($this->nextFile);   // 0 nextFile
        $this->buffer->writeInt($this->endPosition);    // 4 endPosition
        $this->file->fwrite($this->buffer->getData());
        $this->file->fflush();
        $this->writerPosition = self::MESSAGE_START_POSITION;
        $this->readerPosition = self::MESSAGE_START_POSITION;
    }

    public function force()
    {
        $data = $this->buffer->getData();
        if ($data === false) {
            throw new FileBufferException('buffer occur error!');
        }
        $this->file->fseek(0);
        $this->file->fwrite($data);
        $this->file->fflush();
    }

    /**
     * @return int
     */
    public function getNextFile()
    {
        return $this->nextFile;
    }

    /**
     * @return int
     */
    public function getEndPosition()
    {
        return $this->endPosition;
    }

    /**
     * @return int
     */
    public function getCurrentFileNumber()
    {
        return $this->currentFileNumber;
    }

    private function putWriterPosition($pos)
    {
        $this->logIndex->putWriterPosition($pos);
    }

    private function putReaderPosition($pos)
    {
        $this->logIndex->putReaderPosition($pos);
    }

    public function putNextFile($number)
    {
        $this->buffer->position(0);
        $this->buffer->writeInt($number);
        $this->nextFile = $number;
    }

    public function isFull($increment)
    {
        if ($this->fileLimitLength < $this->writerPosition + $increment) {
            return true;
        }
        return false;
    }

    public function write($data)
    {
        $len = strlen($data);
        $increment = $len + 4;
        if ($this->isFull($increment))
        {
            $this->buffer->position(4);
            $this->buffer->writeInt($this->writerPosition);
            $this->endPosition = $this->writerPosition;
            return self::WRITE_FULL;
        }
        $this->buffer->position($this->writerPosition);
        $this->buffer->writeInt($len);
        $this->buffer->writeString($data, $len);
        $this->writerPosition += $increment;
        $this->putWriterPosition($this->writerPosition);
        return self::WRITE_SUCCESS;
    }

    public function readNextAndRemove()
    {
        if ($this->endPosition !== -1 && $this->readerPosition >= $this->endPosition) {
            throw new FileQueueException('file have arrived eof');
        }

        if ($this->readerPosition >= $this->writerPosition) {
            return null;
        }

        $this->buffer->position($this->readerPosition);
        $len = $this->buffer->readInt();
        $this->readerPosition += $len + 4;
        $this->putReaderPosition($this->readerPosition);
        return $this->buffer->readString($len);
    }

    public function close()
    {
        $this->force();
        $this->buffer->clear();
        swoole_timer_clear($this->timer);
        unset($this->buffer);
        unset($this->file);
    }

    function headerInfo()
    {
        return "readerPosition:{$this->readerPosition} writerPosition:{$this->writerPosition} nextFile:{$this->nextFile} endPosition:{$this->endPosition} currentFileNumber:{$this->currentFileNumber}";
    }
}

//测试
//include "/qcloud/www/autoload.php";
//
//$log = new LogIndex('/qcloud/logs/index.log');
//$entity = new LogEntity('/qcloud/logs/entity.log', $log, 1);
//echo $entity->headerInfo() . PHP_EOL;
//echo $log->headerInfo() . PHP_EOL;
//
////var_dump("write:".$entity->write("test1!"));
////var_dump("write:".$entity->write("test2!"));
////var_dump("write:".$entity->write("test3!"));
////var_dump("write:".$entity->write("test4!"));
////$entity->force();
////var_dump("write:".$entity->write("test5!"));
////$entity->force();
////
////var_dump("write:".$entity->write("test6!"));
////var_dump("write:".$entity->write("test7!"));
////var_dump("write:".$entity->write("test8!"));
////$entity->force();
////$log->force();
////echo $log->headerInfo() . PHP_EOL;
//var_dump($entity->readNextAndRemove());
//$entity->force();
//$log->force();
//////echo $entity->headerInfo() . PHP_EOL;
