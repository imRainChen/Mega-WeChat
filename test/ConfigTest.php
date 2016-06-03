<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/28
 * Time: 12:40
 */

include dirname(__DIR__) . "/autoload.php";

var_dump(\Swoole\Util\Config::get('server.log_path'));
