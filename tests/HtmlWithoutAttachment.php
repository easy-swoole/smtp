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
use EasySwoole\Smtp\Message\Html;
use EasySwoole\Smtp\Message\Text;

require_once 'vendor/autoload.php';
go(function () {
    $mailConfig = new MailerConfig();
    $mailConfig->setServer('smtp.exmail.qq.com');
    $mailConfig->setUsername('xxx@xxx.com');
    $mailConfig->setPassword('xxxx');

    $text = new Html();
    $text->setSubject('测试邮件');
    $text->setBody('<b>小菜瓜你哈</b><a href="http://www.baidu.com">百度</a>');
    $mailer = new Mailer($mailConfig);
    try {
        if ($mailer->sendTo('xxx@xxx.com', $text)) {
            echo '发送成功';
        }
    } catch (\Throwable $throwable) {
        echo $throwable->getMessage();
    }
});



