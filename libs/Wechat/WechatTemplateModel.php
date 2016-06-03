<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/4/30 0030
 * Time: 14:55
 */

namespace Wechat;


use Swoole\Util\Config;

class WechatTemplateModel
{
    public $pdo;
    public $prefix;
    public $table = 'mega_wechat_template';

    public function __construct()
    {
        $this->pdo = new \PDO(Config::get('pdo.dsn'), Config::get('pdo.username'), Config::get('pdo.password'));
        $this->pdo->exec("SET NAMES 'utf8';");
        $this->prefix = empty(Config::get('pdo.table_prefix')) ? '' : Config::get('pdo.table_prefix');
    }

    public function getAll($limit = 100)
    {
        $sql = "SELECT * FROM {$this->prefix}{$this->table} LIMIT {$limit}";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTemplate($key)
    {
        $sql = "SELECT * FROM {$this->prefix}{$this->table} WHERE `tmpl_key` = :tmpl_key";
        $statement = $this->pdo->prepare($sql);
        $statement->bindParam(':tmpl_key', $key);
        $statement->execute();
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

}