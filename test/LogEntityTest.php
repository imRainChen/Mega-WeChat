<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/5/19
 * Time: 9:27
 */
include dirname(__DIR__) . "/autoload.php";
$entityPath = __DIR__ . '/queue/megaq6.data';
$path = __DIR__ . '/queue/megaq.log';
$log = new \Swoole\Queue\LogIndex($path);
$entity = new \Swoole\Queue\LogEntity($entityPath, $log, 6, 1024 * 40, null);
echo $entity->headerInfo() . PHP_EOL;
