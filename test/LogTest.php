<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/28
 * Time: 10:58
 */

include "/qcloud/Mega-wechat/autoload.php";

\Swoole\Util\Config::loadConfig('/qcloud/Mega-wechat/config/wechat.ini');
$logger = \Swoole\Util\Log::getLogger();
$logger->error("test");
