<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/5/17
 * Time: 15:48
 */
include dirname(__DIR__) . "/autoload.php";
$wechatApi = new \Wechat\WechatApi('wx80c4bf4cf0dae125', 'e01207cd547f62d73f5099aae83a9f15', 'e01207cd547f62d73f5099aae83a9f15');
$result = $wechatApi->sendTemplateMessage('{
  "touser": "ov6Apv5hsUK7IACd14QpbjDOBtU8",
  "template_id": "gwh2warlCMvXAyw4Pcxg6V7v22CLgnBx9de26DwxJa0",
  "url": "http://weixin.qq.com/download",
  "data": {
    "first": {
      "value": "哈喽，恭喜你购买成功！${ceshi}",
      "color": "#173177"
    },
    "keyword1": {
      "value": "龙虾",
      "color": "#173177"
    },
    "keyword2": {
      "value": "39.8元",
      "color": "#173177"
    },
    "keyword3": {
      "value": "2014年9月22日",
      "color": "#173177"
    },
    "remark": {
      "value": "欢迎再次购买！",
      "color": "#173177"
    }
  }
}');
var_dump($result);
//$result = $wechatApi->sendTemplateMessage([
//    ''
//]);
//var_dump($result);
