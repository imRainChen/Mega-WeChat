<?php

/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/5/19
 * Time: 9:49
 */

include dirname(__DIR__) . "/autoload.php";
$size = 1024 * 1024 * 40;
$buffer = \Swoole\Util\ByteBuffer::allocate(10000);
$buffer->writeInt(4);       //4
$buffer->writeInt(5);       //8
$buffer->position(8);
$buffer->writeInt(5);
$buffer->writeString("aaaaa", 5);  //13
$buffer->position(17);
$buffer->writeInt(5);
$buffer->writeString("bbbbb", 5);  //18
$buffer->writeInt(1);
$buffer->writeString("3", 1);  //23
$buffer->position(4);
$buffer->writeInt(6);

$buffer->position(0);
echo $buffer->readInt() . PHP_EOL;
echo $buffer->readInt() . PHP_EOL;
$len = $buffer->readInt();
echo $buffer->readString($len). PHP_EOL;
$len = $buffer->readInt();
echo $buffer->readString($len). PHP_EOL;
echo $buffer->readString(). PHP_EOL;

$buffer->resetData(pack('l2', -1, -2));
echo $buffer->readInt() . PHP_EOL;
echo $buffer->readInt() . PHP_EOL;

