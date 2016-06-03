<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/15
 * Time: 10:27
 */
namespace Network;

interface EncodeCommand
{
    const SPACE = ' ';
    const SEND_CMD = 'send';
    const RESULT_CMD = 'result';
    const SET_TABLE_CMD = 'tset';
    const PUSH_CMD = 'push';

    function encode();
}