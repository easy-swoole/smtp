# Smtp
---
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
    $config->setPassword('password OR code');
    $config->setMailFrom('mail from');
    $mailer = new Mailer($config);


    $mimeBean = new Html();
    $mimeBean->setSubject('Hello Word!');
    $mimeBean->setBody('<h1>Hello Word</h1>');
    
    //添加附件
    $mimeBean->addAttach(Attach::create('filepath'));
    
    $mailer->sendTo('maile', $mimeBean);
});
```
