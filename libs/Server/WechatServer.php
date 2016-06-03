<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/16
 * Time: 14:49
 */

namespace Server;


use Swoole\Service\Implement\TcpServer;
use Swoole\Util\Config;
use Wechat\WechatTemplateModel;

class WechatServer extends TcpServer
{
    /** @var \swoole_atomic */
    public static $taskActiveNum;
    /**
     * @var \swoole_table 缓存微信模板
     */
    public static $templateTable;

    protected $processName = 'WechatServer';

    public $setting = [
        'open_eof_check' => true,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
        'package_max_length' => 2465792,
    ];

    public function init()
    {
        self::$taskActiveNum = new \swoole_atomic(0);
        self::$templateTable = new \swoole_table(Config::get('server.table_size'));
        self::$templateTable->column('tmpl', \swoole_table::TYPE_STRING, Config::get('server.template_size'));
        self::$templateTable->create();

        $model = new WechatTemplateModel();
        $templates = $model->getAll(Config::get('server.table_size'));
        foreach($templates as $template)
        {
            self::$templateTable->set($template['tmpl_key'], ['tmpl' => $template['template']]);
        }
    }

}