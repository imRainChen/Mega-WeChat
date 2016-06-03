<?php

/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/6/2
 * Time: 10:53
 */
include dirname(__DIR__) . "/autoload.php";


$client = new \swoole_client(SWOOLE_TCP, SWOOLE_SYNC);
$client->set(array(
    'open_length_check' => true, //打开EOF检测
    'package_length_type' => 'N', //设置EOF
    'package_length_offset' => 0,
    'package_body_offset' => 4,
));
$client->connect('127.0.0.1', 9501, 3);
$command = new \Network\PushCommand(1, 'ov6Apv5hsUK7IACd14QpbjDOBtU8', 'z_2hTmxOAJEzQ06eT12S6VUNYq0mLWh1');
$message = $command->encode();
$client->send($message);
$ret = $client->recv();
$ret = $client->recv();
$decoder = new \Network\WechatDecoder();
$result = $decoder->decode($ret);
var_dump($result);
