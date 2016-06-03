<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/27
 * Time: 14:19
 */
include dirname(__DIR__) . "/autoload.php";

//$boolean = new \Network\BooleanCommand(200, 'success', 1);
//$message = $boolean->encode();
//$decoder = new \Network\WechatDecoder();
//$result = $decoder->decode($message);
//
//var_dump($boolean);
//var_dump($result);
//
//$boolean = new \Network\BooleanCommand(200, null, 1);
//$message = $boolean->encode();
//$decoder = new \Network\WechatDecoder();
//$result = $decoder->decode($message);
//
//var_dump($boolean);
//var_dump($result);

$command = new \Network\SetTableCommand(1, null, 'sssssss');
$message = $command->encode();
$decoder = new \Network\WechatDecoder();
$result = $decoder->decode($message, 1);
