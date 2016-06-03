<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/5/11
 * Time: 15:03
 */

include dirname(__DIR__) . "/autoload.php";

$model = new \Wechat\WechatTemplateModel();
$result = $model->getTemplate('aaaa1');
var_dump($result);
$result = $model->getAll();
var_dump($result);
