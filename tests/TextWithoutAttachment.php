<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2019/11/6
 * Time: 13:57
 */

namespace EasySwoole\Smtp\Test;


use EasySwoole\Smtp\Mailer;
use EasySwoole\Smtp\MailerConfig;
use EasySwoole\Smtp\Message\Text;

require_once 'vendor/autoload.php';
go(function () {
    $mailConfig = new MailerConfig();
    $mailConfig->setServer('smtp.exmail.qq.com');
    $mailConfig->setUsername('xxxxx@xxxxx.com');
    $mailConfig->setPassword('xxxxx');

    $text = new Text();
    $text->setSubject('测试邮件');
    $text->setBody('测试邮件正文。。。。。');
    $mailer = new Mailer($mailConfig);
    try {
        if ($mailer->sendTo('xxxxx@xxxxx.com', $text)) {
            echo '发送成功';
        }
    } catch (\Throwable $throwable) {
        echo $throwable->getMessage();
    }
});



