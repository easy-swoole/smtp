# Smtp
---
# 介绍
电子邮件是—种用电子手段提供信息交换的通信方式，是互联网应用最广的服务。电子邮件几乎是每个web应用程序不可或缺的，无论是时事通讯还是订单确认。本库采用swoole协程客户端实现了电子邮件的发送

# 安装
```php
composer require easyswoole/smtp
```
# 用法
```php
use EasySwoole\Smtp\Config;
use EasySwoole\Smtp\Message\Text;
use EasySwoole\Smtp\Message\Html;
use EasySwoole\Smtp\Message\Alternative;
use EasySwoole\Smtp\Network\Config as ClientConfig;
use EasySwoole\Smtp\Mailer;
\Swoole\Coroutine::create(function () {
    $dir = dirname(__FILE__);
    $config = new Config();
    $config->setServer('smtp.qq.com');
    $config->setPort(465);
    $config->setSsl(true);
    $config->setUsername('xxx');
    $config->setPassword('xxxxx');
    $config->setMailFrom('xxxxx');

//若是需要修改发送超时时间和包大小
    $clientConfig = new ClientConfig();
    $clientConfig->setTimeout(5);
    $clientConfig->setMaxPackage(5 * 1024 * 1024);

//    $body = (new Text('这是文本'))
//    $body = (new Html('这是文本<div>这是div<img src="http://image.biaobaiju.com/uploads/20190521/17/1558430155-SDYrJnBOFK.png"></div>'))
    $body = (new Alternative('这是文本<div>这是html<img src="http://image.biaobaiju.com/uploads/20190521/17/1558430155-SDYrJnBOFK.png"></div>'))
        ->addAttachment('123.jpg', $dir . '/123.jpg')
        ->addAttachment('1232.jpg', $dir . '/123.jpg')
        ->addAttachment('1234.xlsx', $dir . '/1234.xlsx');
    $mailer = new Mailer($config,$clientConfig);
    $mailer->setSubject('测试邮件')
        ->addTo('xxxxx')
        ->send($body);
});
```
