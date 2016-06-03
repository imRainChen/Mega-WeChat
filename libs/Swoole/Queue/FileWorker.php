<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/18
 * Time: 11:23
 */

namespace Swoole\Queue;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Swoole\Util\Log;
use Swoole\Util\MessagePacker;
use swoole_process;

/**
 *
 * 删除和预创建文件worker
 *
 * Class FileWorker
 * @package Swoole\Queue
 * @property string $baseDir
 * @property integer $fileLimitLength
 * @property integer $pid
 * @property swoole_process $process
 */
class FileWorker
{
    private $baseDir;
    private $pid;
    private $process;

    public function __construct($baseDir)
    {
        $process = new \swoole_process(function(swoole_process $worker) use ($baseDir) {
            cli_set_process_title('mega-queue worker');
            $logger = Log::getLogger();
            while (true) {
                $receive = $worker->pop();
                if ($receive !== false) {
                    $data = json_decode($receive, true);
                    if (array_key_exists('delete', $data)) {
                        $filePath = $baseDir . $data['delete'] . ".data";
                        unlink($filePath);
                    } else if (array_key_exists('create', $data)) {
                        $filePath = $baseDir . $data['create'] . ".data";
                        if (!FileWorker::create($filePath)) {
                            $logger->error('pre-create file have failed');
                        }
                    } else if (array_key_exists('close', $data)) {
                        $worker->exit($data['close']);
                    } else {
                        sleep(10);
                    }
                } else {
                    sleep(10);
                }
            }
        }, false, false);
        $key = ftok(dirname($baseDir), '1');
        $process->useQueue($key);   // 使用IPC队列
        $this->process = $process;
        $this->baseDir = $baseDir;
    }

    public function start()
    {
        return $this->pid = $this->process->start();
    }

    public function close()
    {
        $data = json_encode(['close' => 0]);
        $this->process->push($data);
        swoole_process::wait();
        unset($this->process);
    }

    /**
     * 添加删除文件到队列
     * @param $path
     */
    public function addDeleteFile($path)
    {
        $data = json_encode(['delete' => $path]);
        $this->process->push($data);
    }

    /**
     * 添加预创建文件到队列
     * @param $fileNum
     */
    public function addCreateFile($fileNum)
    {
        $data = json_encode(['create' => $fileNum]);
        $this->process->push($data);
    }

    /**
     * 创建队列文件
     * @param $path
     * @return bool
     */
    public static function create($path)
    {
        if (is_file($path) === false) {
            // 0 nextFile 4 endPosition
            $buffer = pack('l2', -1, -2);
            return file_put_contents($path, $buffer) === false ? false : true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getBaseDir()
    {
        return $this->baseDir;
    }

    /**
     * @return swoole_process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } else {
            throw new \ErrorException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

}

//include "autoload.php";
//
//$key =ftok('/qcloud/www/Swoole/Queue', 1);
//if (msg_queue_exists($key)) {
//    $resource = msg_get_queue($key);
//    msg_remove_queue($resource);
//}
//
//
//$worker = new \Swoole\Queue\FileWorker('/qcloud/logs/test');
//$worker->start();
//$worker->addCreateFile(1);
//$worker->addCreateFile(2);
//$worker->addCreateFile(3);
//
//$worker->close();
//sleep(10);
//swoole_process::wait();
//unset($worker);