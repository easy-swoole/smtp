# Smtp
---
# 介绍
几乎每个web应用程序都需要发送电子邮件，无论是时事通讯还是订单确认，这个库提供了必要的工具。

# 安装
```php
composer require easyswoole/smtp
```
# 用法
```php
use EasySwoole\Smtp\Mailer;
use EasySwoole\Smtp\MailerConfig;
use EasySwoole\Smtp\Message\Html;
use EasySwoole\Smtp\Message\Attach;
go(function (){
    
    $config = new MailerConfig();
    $config->setServer('smtp.163.com');
    $config->setSsl(true);
    $config->setUsername('username');
    $config->setPassword('password');
    $config->setMailFrom('mail from');
    $config->setTimeout(10);//设置客户端连接超时时间
    $config->setMaxPackage(1024*1024*5);//设置包发送的大小：5M
    
    //设置文本或者html格式
    $mimeBean = new Html();
    $mimeBean->setSubject('Hello Word!');
    $mimeBean->setBody('<h1>Hello Word</h1>');
    
    //添加附件
    $mimeBean->addAttach(Attach::create('filepath'));
    
    
    $mailer = new Mailer($config);
    $mailer->sendTo('maile', $mimeBean);
});
```
