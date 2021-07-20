<?php

require_once '../vendor/autoload.php';

go(function () {
    $mail = new \EasySwoole\Smtp\Mailer(true);
    $mail->setTimeout(5);
    $mail->setHost("smtp.qq.com");
    $mail->setPort(465);
    $mail->setSsl(true);
    $mail->setUsername("975975398@qq.com");
    $mail->setFrom("975975398@qq.com", '975975398');
    $mail->setPassword("bkzeoffythrrbebd");
    $mail->addAddress("747883372@qq.com", "747883372");
    $mail->setReplyTo("gaobinzhan@gmail.com","stitch");
    $text = new \EasySwoole\Smtp\Request\Html();
    $text->setSubject("Smtp Test");
    $text->setBody("<h1>Smtp Test</h1>");
    $text->addAttachment(__FILE__,"A1a大小.php");
    $mail->send($text);
});