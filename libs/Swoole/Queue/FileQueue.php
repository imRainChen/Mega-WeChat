<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/11
 * Time: 16:57
 */
namespace Swoole\Queue;
use Monolog\Logger;
use Swoole\Util\Log;

/**
 * 文件队列，基于swoole_buffer和swoole_timer实现
 * Class FileQueue
 * @package Swoole\Queue
 */
class FileQueue implements Queue
{
    const FILE_PREFIX = 'megaq';
    const LOG_NAME = 'megaq.log';

    private static $logger;

    private $fileLimitLength = 1024 * 1024 * 40;
    private $path = null;

    private $logIndex = null;
    private $writerHandle = null;
    private $readerHandle = null;

    private $readerIndex = -1;
    private $writerIndex = -1;

    private $fileWorker;

    public function __construct($dir, $fileLimitLength = 1024 * 1024 * 40)
    {
        if (!self::$logger) {
            self::$logger = Log::getLogger();
        }
        $this->fileLimitLength = $fileLimitLength;
        if (!is_dir($dir)) {
            if (!mkdir($dir)) {
                throw new \ErrorException('create dir have failed');
            }
        }

        $this->path = $dir;
        $this->logIndex = new LogIndex($dir . DIRECTORY_SEPARATOR . self::LOG_NAME);
        $this->writerIndex = $this->logIndex->getWriterIndex();
        $this->readerIndex = $this->logIndex->getReaderIndex();
        $writerFileName = $dir . DIRECTORY_SEPARATOR . self::FILE_PREFIX . $this->writerIndex . ".data";
        $this->fileWorker = new FileWorker($this->path . DIRECTORY_SEPARATOR . self::FILE_PREFIX);
        $this->fileWorker->start();
        $this->writerHandle = $this->createLogEntity($writerFileName, $this->logIndex, $this->writerIndex);
        if ($this->readerIndex === $this->writerIndex) {
            $this->readerHandle = $this->writerHandle;
        } else {
            $readerFileName = $dir . DIRECTORY_SEPARATOR . self::FILE_PREFIX . $this->readerIndex . ".data";
            $this->readerHandle = $this->createLogEntity($readerFileName, $this->logIndex, $this->writerIndex);
        }

    }

    /**
     * @param $path
     * @param $logIndex
     * @param $fileNumber
     * @return LogEntity
     */
    private function createLogEntity($path, $logIndex, $fileNumber)
    {
        return new LogEntity($path, $logIndex, $fileNumber, $this->fileLimitLength, $this->fileWorker);
    }

    /**
     * 翻滚文件
     * 一个文件的数据写入达到fileLimitLength的时候，滚动到下一个文件实例
     */
    private function rotateNextLogWriter()
    {
        $this->writerIndex += 1;
        $this->writerHandle->putNextFile($this->writerIndex);
        if ($this->readerHandle != $this->writerHandle) {
            $this->writerHandle->close();
        }
        $this->logIndex->putWriterIndex($this->writerIndex);
        $writerFileName = $this->path . DIRECTORY_SEPARATOR . self::FILE_PREFIX . $this->writerIndex . ".data";
        $this->writerHandle = $this->createLogEntity($writerFileName, $this->logIndex, $this->writerIndex);
    }

    /**
     * 放入数据到队列
     * @param $data
     * @return bool
     */
    public function push($data)
    {
        $status = $this->writerHandle->write($data);
        if ($status === LogEntity::WRITE_FULL) {
            $this->rotateNextLogWriter();
            $status = $this->writerHandle->write($data);
        }

        if ($status === LogEntity::WRITE_SUCCESS) {
            $this->logIndex->increaseSize();
        }

        if ($status === LogEntity::WRITE_FAILURE) {
            return false;
        }

        return true;
    }

    /**
     * 队列中取出数据
     * @return null|string
     */
    public function pop()
    {
        $result = null;
        try {
            $result = $this->readerHandle->readNextAndRemove();
        } catch (FileQueueException $ex) {
            $deleteNum = $this->readerHandle->getCurrentFileNumber();
            $nextFile = $this->readerHandle->getNextFile();
            $this->readerHandle->close();
            $this->fileWorker->addDeleteFile($deleteNum);
            // 更新下一次读取的位置和索引
            $this->logIndex->putReaderPosition(LogEntity::MESSAGE_START_POSITION);
            $this->logIndex->putReaderIndex($nextFile);
            if ($this->writerHandle->getCurrentFileNumber() === $nextFile) {
                $this->readerHandle = $this->writerHandle;
            } else {
                $readerFileName = $this->path . DIRECTORY_SEPARATOR . self::FILE_PREFIX . $nextFile . ".data";
                $this->readerHandle = $this->createLogEntity($readerFileName, $this->logIndex, $nextFile);
            }
            try {
                $result = $this->readerHandle->readNextAndRemove();
            } catch (FileQueueException $ex) {
                self::$logger->error('read new log file FileEOFException error occurred');
            }
        }
        if ($result !== null) {
            $this->logIndex->decreaseSize();
        }
        return $result;
    }

    public function clear()
    {
        while ($this->pop() != null);
    }

    public function close()
    {
        if ($this->readerHandle !== $this->writerHandle) {
            $this->writerHandle->close();
        }
        $this->readerHandle->close();
        $this->logIndex->close();  //关闭索引保存，若关闭队列，则数据重新开始存储
        $this->fileWorker->close();
    }

    /**
     * 获取队列大小
     * @return int
     */
    public function count()
    {
       return $this->logIndex->getSize();
    }
}