<?php
/**
 * Created by PhpStorm.
 * User: runs
 * Date: 19-1-28
 * Time: 下午11:29
 */

require_once 'vendor/autoload.php';

go(function (){
    $config = new \EasySwoole\Smtp\MailerConfig();
    $config->setServer('smtp.163.com');
    $config->isStartSSL(true);
    $config->setUsername('username');
    $config->setPassword('password OR code');
    $config->setMailFrom('mail from');
    $mailer = new \EasySwoole\Smtp\Mailer($config);


    $mimeBean = new \EasySwoole\Smtp\Message\Html();
    $mimeBean->setSubject('Hello Word!');
    $mimeBean->setBody('<h1>Hello Word</h1>');

    $mailer->mail('maile', $mimeBean);
});