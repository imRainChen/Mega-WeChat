<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/17
 * Time: 11:05
 */

namespace Network;

use Monolog\Logger;
use Server\WechatServer;
use Swoole\Command\ResponseStatus;
use Swoole\Exception\ErrorException;
use Swoole\Protocol\Base;
use Swoole\Queue\FileQueue;
use Swoole\Util\Config;
use Swoole\Util\Log;
use Wechat\Template;
use Wechat\WechatApi;
use Wechat\WechatTemplateModel;

class WechatProtocol extends Base
{
    private $taskWorkerNum;
    /** @var FileQueue  */
    private $queue;
    /** @var WechatApi */
    private $wechatApi;
    /** @var Logger */
    private $logger;

    public function init()
    {
        parent::init();
        $this->setCodecFactory(new WechatCodecFactory());
        $this->wechatApi = new WechatApi(Config::get('wechat.app_id'),
                        Config::get('wechat.app_secret'), Config::get('wechat.token'));
        $this->logger = Log::getLogger();
    }

    /**
     * @param $server \swoole_server
     * @param $workerId
     */
    function onStart(\swoole_server $server, $workerId)
    {
        // worker进程
        if (!$server->taskworker) {
            $filePath = Config::get('server.queue_file_path') . '/task-' . $workerId;
            $this->queue = new FileQueue($filePath);
            $this->taskWorkerNum = $this->server->setting['task_worker_num'];

            // 定时检测队列是否有未处理的数据
            $server->tick(10000, function() use ($server) {
                if (WechatServer::$taskActiveNum->get() < $this->taskWorkerNum) {
                    if (($message = $this->queue->pop()) !== null) {
                        WechatServer::$taskActiveNum->add(1);
                        $server->task($message);
                    }
                }
            });
        }
    }

    function onConnect(\swoole_server $server, $fd, $from_id)
    {
        $this->logger->info("Client@[{$fd}:{$from_id}] connect");
    }

    function onReceive(\swoole_server $server, $fd, $from_id, $data)
    {
        try {
            $command = $this->decode($data, $fd);

            if ($command instanceof SendCommand) {
                $this->logger->info(__LINE__ . " SendCommand {$command->toString()}");
                // 若微信模板消息存在，则放入队列
                if (WechatServer::$templateTable->exist($command->getKey())) {
                    $this->queue->push(serialize($command));
                } else {
                    $message = new BooleanCommand(HttpStatus::BAD_REQUEST, 'template key not exists', $command->getOpaque());
                    $server->send($command->getFd(), $this->encode($message));
                }
            } else if ($command instanceof PushCommand) {
                $this->logger->info(__LINE__ . " PushCommand {$command->toString()}");
                if (WechatServer::$templateTable->exist($command->getKey())) {
                    $this->queue->push(serialize($command));
                    $message = new BooleanCommand(HttpStatus::SUCCESS, null, $command->getOpaque());
                    $server->send($fd, $this->encode($message));
                } else {
                    $message = new BooleanCommand(HttpStatus::BAD_REQUEST, 'template key not exists', $command->getOpaque());
                    $server->send($command->getFd(), $this->encode($message));
                }
            } else if ($command instanceof SetTableCommand) {
                try {
                    $this->logger->info(__LINE__ . " SetTableCommand {$command->toString()}");
                    $model = new WechatTemplateModel();
                    $template = $model->getTemplate($command->getKey());
                    if ($template !== false) {
                        $message = new BooleanCommand(HttpStatus::SUCCESS, null, $command->getOpaque());
                        if (WechatServer::$templateTable->exist($template['tmpl_key'])) {
                            WechatServer::$templateTable->set($template['tmpl_key'], ['tmpl' => $template['template']]);
                        } else {
                            if (count(WechatServer::$templateTable) < Config::get('server.table_size')) {
                                WechatServer::$templateTable->set($template['tmpl_key'], ['tmpl' => $template['template']]);
                            } else {
                                $message = new BooleanCommand(HttpStatus::BAD_REQUEST, 'over table size', $command->getOpaque());
                            }
                        }
                    } else {
                        $message = new BooleanCommand(HttpStatus::BAD_REQUEST, 'template key not exists', $command->getOpaque());
                    }
                } catch (\Exception $ex) {
                    $message = new BooleanCommand(HttpStatus::INTERNAL_SERVER_ERROR, $ex->getMessage(), $command->getOpaque());
                    $this->logger->error(__LINE__ . ' SetTableCommand ' . $ex->getMessage());
                }
                $server->send($command->getFd(), $this->encode($message));
                if ($message->getCode() === HttpStatus::INTERNAL_SERVER_ERROR) {
                    $server->close($command->getFd());
                }
            }

            if (WechatServer::$taskActiveNum->get() < $this->taskWorkerNum) {
                WechatServer::$taskActiveNum->add(1);
                $server->task($this->queue->pop());
            }
        } catch (CommandException $ex) {
            $message = new BooleanCommand(HttpStatus::INTERNAL_SERVER_ERROR, $ex->getMessage(), null);
            $this->logger->warning($ex->getMessage(), $server->connection_info($fd, -1, true));
            $server->send($fd, $this->encode($message));
            $server->close($fd);
        }
    }

    function onShutdown(\swoole_server $server, $workerId)
    {
        if (!$server->taskworker) {
            $this->queue->close();
        }
    }

    function onTask(\swoole_server $server, $taskId, $fromId, $data)
    {
        $command = unserialize($data);
        if ($command instanceof SendCommand or $command instanceof PushCommand) {
            try {
                $template = new Template();
                $template->setOpenid($command->getOpenId());
                $template->setData($command->getData());
                $templateData = WechatServer::$templateTable->get($command->getKey())['tmpl'];
                $template->setTemplate($templateData);
                $template = $template->parse();
                $result = $this->wechatApi->sendTemplateMessage($template);
                // SendCommand需要响应调用API接口请求
                if ($command instanceof SendCommand) {
                    //$result = 1; //测试使用
                    if ($result['errcode'] === 0) {
                        $message = new BooleanCommand(HttpStatus::SUCCESS, null, $command->getOpaque());
                    } else {
                        $this->logger->info(__LINE__ . ' sendTemplateMessage fail', $result);
                        $result['openid'] = $command->getOpenId();
                        $message = new BooleanCommand(HttpStatus::BAD_REQUEST, json_encode($result), $command->getOpaque());
                    }
                    $server->send($command->getFd(), $this->encode($message));
                }
            } catch (\Exception $ex) {
                $this->logger->error(__LINE__ . ' SendCommand ' . $ex->getMessage());
                $message = new BooleanCommand(HttpStatus::INTERNAL_SERVER_ERROR, $ex->getMessage(), $command->getOpaque());
                $server->send($command->getFd(), $this->encode($message));
                $server->close($command->getFd());
            }
        }
        WechatServer::$taskActiveNum->sub(1);
        $server->finish("finish");
    }

    /**
     * @param $server \swoole_server
     * @param $taskId
     * @param $data
     */
    function onFinish(\swoole_server $server, $taskId, $data)
    {
        if (WechatServer::$taskActiveNum->get() < $this->taskWorkerNum) {
            if (($message = $this->queue->pop()) !== null) {
                WechatServer::$taskActiveNum->add(1);
                $server->task($message);
            }
        }
    }

    function onRequest($request, $response)
    {
        throw new \Exception('not implement onRequest method');
    }

    function onClose(\swoole_server $server, $client_id, $from_id)
    {
        //echo "Client@[{$client_id}:{$from_id}] close" . PHP_EOL;
        $this->logger->info("Client@[{$client_id}:{$from_id}] close");
    }

    function onTimer(\swoole_server $server, $interval)
    {
        throw new \Exception('not implement onTimer method');
    }

}