<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2020/4/14
 * Time: 13:31
 */
require_once 'vendor/autoload.php';

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
    $config->setUsername('xxxxxx@qq.com');
    $config->setPassword('xxxxxx');
    $config->setMailFrom('xxxxxx@qq.com');

    $clientConfig = new ClientConfig();
    $clientConfig->setTimeout(5);
    $clientConfig->setMaxPackage(5 * 1024 * 1024);


//    $body = (new Text('这是文本'))
//    $body = (new Html('这是文本<div>这是div<img src="http://image.biaobaiju.com/uploads/20190521/17/1558430155-SDYrJnBOFK.png"></div>'))
    $body = (new Alternative('这是文本<div>这是html<img src="http://image.biaobaiju.com/uploads/20190521/17/1558430155-SDYrJnBOFK.png"></div>'))
        ->addAttachment('123.jpg', $dir . '/123.jpg')
        ->addAttachment('1232.jpg', $dir . '/123.jpg')
        ->addAttachment('1234.xlsx', $dir . '/1234.xlsx');
    $mailer = new Mailer($config, $clientConfig);
    $mailer->setSubject('测试邮件')
        ->addTo('xxxxxx@qq.com')
        ->addTo('xxxxxx@qq.com')
        ->send($body);
});

