<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2019/11/6
 * Time: 15:24
 */

namespace EasySwoole\Smtp\Test;


use EasySwoole\Smtp\Mailer;
use EasySwoole\Smtp\MailerConfig;
use EasySwoole\Smtp\Message\Attach;
use EasySwoole\Smtp\Message\Html;
use EasySwoole\Smtp\Message\Text;

require_once 'vendor/autoload.php';

go(function () {
    $mailConfig = new MailerConfig();
    $mailConfig->setServer('smtp.exmail.qq.com');
    $mailConfig->setUsername('xxxxxx@xxxxxx.com');
    $mailConfig->setPassword('xxxxxx');

    $text = new Html();
    $text->setSubject('测试邮件');
    $text->setBody('<b>小菜瓜你哈</b><a href="http://www.baidu.com">百度</a>');

    $filename = dirname(dirname(__FILE__)) . '/zhangyuying.jpg';

    $text->addAttachment(Attach::create($filename));

    $filename = dirname(dirname(__FILE__)) . '/2019-07-04.xls';

    $text->addAttachment(Attach::create($filename));

    $mailer = new Mailer($mailConfig);
    try {
        if ($mailer->sendTo('xxxxxx@xxxxxx.com', $text)) {
            echo '发送成功' . "\r\n";
        }
    } catch (\Throwable $throwable) {
        echo $throwable->getMessage();
    }
});