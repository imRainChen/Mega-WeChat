<?php
/**
 * Created by PhpStorm.
 * User: Rain
 * Date: 2016/2/10 0010
 * Time: 21:51
 */

namespace Swoole\Service\Implement;

use Swoole\Service\Driver;

class TcpServer extends Server implements Driver
{
    protected $sockType = SWOOLE_SOCK_TCP;
    protected $setting = array(
        //'open_cpu_affinity' => 1,
        //'open_tcp_nodelay' => 1,
    );
}