Mega-Wechat
==========

Mega-Wechat是一款发送微信模板消息的服务，基于Swoole网络框架实现。支持大量的消息发送，并发执行发送模板消息接口，整个发送过程按照先来先服务的队列执行。支持定制模板消息，随时改随时用。

功能特性
----------
 - 发送微信模板消息
 - 多进程执行发送模板消息接口 
 - 队列时序性
 - 文件消息队列存储
 - 基于Swoole高性能网络框架
 - 发送消息完成，通知客户端。

使用场景
----------

 - 对业务上发送模板消息的解耦。
 - 即时发送模板消息请求，无需等待微信API调用耗时。（前提是对发送成功或失败不关心）
 - 实现可控制发送进度的模板消息任务。
 - 随时修改模板消息内容，方便运营操作。

设计初衷
----------
在公司里运营需要经常发送微信模板消息到指定的用户，那时候其他业务的实现比较紧急，所以仅仅是简单的写死一些模板消息到controller里面，用的时候执行下命令完成运营的需求。因此也造成改模板内容的时候需要改代码并用git更新，而且由于单进程的问题大量的模板消息发送会非常耗时（主要是curl调用微信接口的耗时）。

**对此想到了几种解决方案：**

第一种直接实现一个多进程的client通过curl调用微信发送模板消息API，这种方法实现起来简单快捷，但无法支持其它业务调用模板消息的需求。

第二种是由一个进程分发任务fork多个子进程，通过子进程不断轮询redis队列，这种方案也是实现起来也是比较简单，但可控性太差基本上是很难控制的。

第三种也是目前使用的方案，是通过swoole实现一个类似于消息队列的服务，由多个task执行慢速的curl调微信API的操作，并且可以返回执行后的结果给到客户端。由于swoole是一个非常强大的网络框架，能接收很大并发，理论上来说大量的发送模板消息请求，swoole都可以撑得住，但因为微信的发送模板消息API耗时比较高，大量的请求投递到task中执行，由于处理的速度比不上接收的速度，将会导致缓冲区溢出，所以Mega-Wechat里用上了文件队列，将请求都先投入到队列中，等待task进程空闲时，从队列中取出请求并投递到task中处理发送模板消息请求。这样的实现就不会导致缓冲区溢出，而且还能支撑大量的并发。但是由于微信对模板消息有一套规则限制，所以大量的调用API仅仅是理论上的。

系统架构
----------
Mega-Wechat系统架构如下图所示：

![mega-wechat服务架构](http://img.blog.csdn.net/20160601212545358)

**系统执行过程描述：**

1. 客户端发送模板命令请求到服务端。
2. 服务端Worker进程接收到命令后会解析命令并push到队列中。
3. 从队列中pop出已在队列中的请求，并投入到task进程处理请求。（由于task进程有限，大量的发送模板请求将会缓存到队列中，等待task进程空闲后继续从队列pop出请求后投入。）
4. 在task进程中执行发送请求处理，主要是调用微信模板消息接口等待接口响应。
5. 对微信响应的结果进行成功或失败的相应处理后，再把结果响应给客户端。

以上描述是一个同步的过程，对于客户端而言可以是异步或同步的处理。

目录结构
----------

```
config/				服务器配置
libs/				
	Network/
	Server/			
	Swoole/			Mega核心类库
	Wechat/			微信API
logs/
vendor/				支持composer，依赖monolog写日志
autoload.php
mega				Mega命令入口
```

介绍
----------

###环境要求
 - php5.6+
 - Swoole1.8.2+
 - Mysql
 - Linux系统

###安装

**第一步**
安装PHP，需要5.6以上版本。由于服务端的队列用了SPL函数和PHP新特性的语法

**第二步**
安装Mysql，什么版本都可以。

> yum install mysql

安装成功Mysql需要创建一张mega_wechat_template表，[详细结构由下一章节介绍](#服务端对Mysql的依赖)

**第三步**
安装swoole扩展前必须保证系统已经安装了下列软件

> php-5.3.10 或更高版本
> gcc-4.4 或更高版本
> make
> autoconf 

下载地址
[https://github.com/swoole/swoole-src/releases](https://github.com/swoole/swoole-src/releases)
[http://pecl.php.net/package/swoole](http://pecl.php.net/package/swoole)
[http://git.oschina.net/matyhtf/swoole](http://git.oschina.net/matyhtf/swoole)

下载源代码包后，在终端进入源码目录，执行下面的命令进行编译和安装

> cd swoole
> phpize ./configure
> make
> make install

###服务端对Mysql的依赖

在Mega-Wechat的服务端里对微信模板做了存储。这是因为大部分的业务需要经常修改模板的内容，对于这些经常需要改变的模板，如果写死到程序里是非常的不方便，所以利用了MySql存储模板和额外添加了一些业务需要的字段。

对于服务端而言启动时会对数据库的模板进行缓存，若需要更新模板也有相应的命令实时更新服务端的模板缓存，因此不需要担心每次发送模板时都需要从数据库中获取模板造成性能下降的问题。

**表结构：**
``` mysql
CREATE TABLE `mega_wechat_template` (
  `tmpl_key` char(32) NOT NULL COMMENT '模板key',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '模板标题',
  `template` text NOT NULL COMMENT '模板内容',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tmpl_key`)
)
```
**字段说明：**

字段        | 说明 |
----------- | -------------
tmpl_key    | 模板key（作为发送Mega模板命令请求的参数）
title       | 模板标题
template    | 模板内容，存储格式为json
created_at  | 创建时间
updated_at  | 更新时间

``` json
template字段格式，例子如下：

{
    "touser":"${OPENID}",
    "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
    "url":"http://weixin.qq.com/download",            
    "data":{
        "first": {
            "value":"恭喜你购买成功！",
            "color":"#173177"
        },
        "keynote1":{
            "value":"巧克力",
            "color":"#173177"
        },
        "keynote2": {
            "value":"39.8元",
            "color":"#173177"
        },
        "keynote3": {
            "value":"2014年9月22日",
            "color":"#173177"
        },
        "remark":{
            "value":"欢迎再次购买！",
            "color":"#173177"
        }
    }
}

注意：JSON中的${OPENID}，是自定义的变量，调用微信模板消息接口前会先解析json模板并替换相应的自定义变量。
该变量由发送Mega模板命令请求的参数定义
```

###配置
配置文件统一放在config目录下，每个server独立一个配置。
``` ini
[server]
;启动server类
class = "Server\MegaWechatServer"
;协议类
protocol = "Network\MegaWechatProtocol"
;主机和端口
listen[] = 127.0.0.1:9501
;缓存模板个数
table_size = 100;
;缓存模板内容最大size
template_size = 4048
;文件队列存储路径
queue_file_path = "/Mega-Wechat/logs/queue"

[setting]
;;;;;;;;;;;;swoole配置;;;;;;;;;;;;;;
dispatch_mode = 2
worker_num = 2
task_worker_num = 8

open_eof_check = true
package_length_type = N
package_length_offset = 0
package_body_offset = 4
package_max_length = 2465792

daemonize = 1
;;swoole_log文件
;log_file = "/qcloud/logs/swoole.log"

[pdo]
dsn = "mysql:host=127.0.0.1;dbname=mega"
username = root
password = i201314
table_prefix = mega_

[wechat]
app_id = wx10c4b54cf0aae125
app_secret = e01207cc547f62d73f5099aae83a9f15
token = e01207cd547f62d73f5099cae83a9f15

[log]
;;;;;;系统log配置，基于monolog;;;;;;
log_file = /Mega-Wechat/logs/mega.log
;存储日志级别
log_level = warning
;日志前缀
log_prefix = mega

```
###使用

根据配置文件启动server，以每个.ini文件作为服务名和配置，如config/wechat.ini配置文件：

``` php
cd Mega-Wechat

//php mega ${配置文件名} ${cmd}
php mega wechat start //开启服务 
php mega wechat stop //关闭服务
php mega wechat restart //重启服务
```

通讯协议
----------
Mega-Wechat通信走的是TCP，协议采用固定包头+包体的协议设计。通用的协议格式如下：

```php
{packLength}{headerLength}{command} {params}{opaque}{bodyLength}{body}
```
*注意：{command} {params}中间的空格，每个params参数都用空格隔开。*

###详细协议介绍

####**Send**
发送模板消息命令协议，每条命令代表一次微信模板消息发送。客户端发送一条Send命令到服务端，它会执行完一次微信模板消息API后把结果再响应到客户端，返回一条ACK确认。

对应类为：Network\SendCommand
```php
/**
 * @param $opaque int 发送序号
 * @param $openid string 微信openid
 * @param $key string 模板key
 * @param $data array 自定义变量，可选，默认为null，例子如下：
 *    传入数组为：['mega' => 'wechat']
 *    模板内容中包含一个${mega}自定义变量，则数组中的mega值会替换到相应变量中。
 * @param $fd int 客户端标志，可选，默认为null
 */
new SendCommand($opaque, $openid, $key, $data, $fd)
```

####**Push**
模板消息入队命令协议。服务端接收到该命令后会立即入队并响应ACK确认，后续会根据队列排队后执行发送微信模板消息处理。

对应类为：Network\PushCommand
```php
/**
 * @param $opaque int 发送序号
 * @param $openid string 微信openid
 * @param $key string 模板key
 * @param $data array 自定义变量，可选
 */
new PushCommand($opaque, $openid, $key, $data)
```

####**TSet**
设置模板缓存命令协议。服务端接收到该命令后会根据key从数据库中获取模板内容并缓存到内存中，若key不存在或者超出缓存大小会响应相关错误信息。

对应类为：Network\SetTableCommand
```php
/**
 * @param $opaque int 发送序号
 * @param $key string 模板key
 * @param $fd int 客户端标志，客户端发送命令时可为null
 */
new SetTableCommand($opaque, $key, $fd)
```

####**Result**
通用应答命令协议，返回请求结果。该协议可作为ACK确认，根据返回的opaque值作为上次请求的应答。返回的code作为应答码，采用HTTP协议应答状态码一样的语义。若是错误的响应通常会带上message字段。例如Send命令发送失败后会响应code为400，message为微信模板消息接口返回的json作为应答。

对应类为：Network\BooleanCommand
```php
/**
 * @param $code int 应答状态码
 * @param $message string 消息内容
 * @param $opaque int 应答序号
 */
new BooleanCommand($code, $message, $opaque)
```

客户端实现思路
----------
Mega-Wechat暂时未开源客户端实现，这部分公司正在运行当中，会在以后开源出来。不过对此会给大家写两个思路，第一种是利用Send协议，另一种是Push协议，这两者可以应对不同的场景。

####Push场景
Push协议主要是对于不希望等待微信调用API耗时的实现。具体来说Push会把每一次发送模板消息放入队列后，Server端就会立刻作出应答，此时客户端就可以继续运行业务逻辑，而不需要关心这条发送模板消息是否成功。（Server端可以保证消费这条消息）

####Send场景
Send协议也是发送模板消息，不同于Push的是Server端会在调用完成微信模板消息API后将结果作为应答。客户端收到应答后可获取发送的结果（应答是Result协议，成功会返回code是200，而失败会返回code是400，message为微信返回结果的json），根据结果客户端可做相应的业务逻辑处理。

**Send协议会存在几点问题：**

 1. 若Server端存在大量的发送任务，将会导致客户端未能很快的接收到服务端应答。
 2. 使用同步客户端实现，可能会导致接收应答超时（由于上一点的原因），而异步没有这个问题。
 3. 效率无Push高，因为Push是不能用等待发送情况的。

相对于以上几点问题而言，该协议的优点是能得知发送的结果，并能对是否继续发送下条消息做业务逻辑控制。

**实际场景：**
需要对指定一批用户或所有用户批量发送模板消息，可利用这个协议实现。可以把发送一批模板消息给用户看作是一次任务，该任务包含发送数量，成功数，失败数，模板key等等的数据。通过客户端实现发送，接收和记录发送过程的业务逻辑。每次的服务端的应答作为更新成功还是失败的依据。具体业务流程如下图：

![这里写图片描述](http://img.blog.csdn.net/20160603172251483)

以上业务逻辑推荐使用Swoole异步客户端实现，并且运行后可将客户端作为守护进程后台运行，需要结束时可用kill关闭。

附上一张客户端实现效果图：

![这里写图片描述](http://img.blog.csdn.net/20160603170000551)


贡献
----------
如果有什么建议欢迎联系，[也可发布问题和反馈。](https://github.com/imRainChen/Mega-Wechat/issues)

Email：chenjiarong448@qq.com

License
----------
Apache License Version 2.0 see http://www.apache.org/licenses/LICENSE-2.0.html
