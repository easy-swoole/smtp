# Smtp

## 介绍

电子邮件是—种用电子手段提供信息交换的通信方式，是互联网应用最广的服务。电子邮件几乎是每个web应用程序不可或缺的，无论是时事通讯还是订单确认。本库采用swoole协程客户端实现了电子邮件的发送

## 安装

```bash
composer require easyswoole/smtp
```

## 用法

### 基础配置

```php
$mail = new \EasySwoole\Smtp\Mailer(false);
``` 

参数:

- `$enableException` 是否启用异常 默认`false`

#### 设置超时

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
$mail->setTimeout(5);
```

#### 设置最大数据包大小

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
$mail->setMaxPackage(1024 * 1024 * 2);
```

#### 设置Host

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
$mail->setHost("smtp.qq.com");
```

#### 设置Port

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
$mail->setPort(465);
```

#### 设置Ssl

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
$mail->setSsl(true);
```

#### 设置用户名及密码

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
$mail->setUsername("xxx@qq.com");
$mail->setPassword("xxxxx");
```

#### 设置发件人地址

> 可选方法 默认用户名

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
$mail->setFrom("xxx@qq.com");
```

参数:

- `$address` 发件人地址
- `$name` 设置昵称 可选参数

#### 设置收件人地址

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
$mail->addAddress("xxx@qq.com");
```

参数:

- `$address` 收件人地址
- `$name` 设置昵称 可选参数

#### 设置回复地址

> 可选方法 默认发件人地址

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
$mail->setReplyTo("xxx@qq.com");
```

参数:

- `$address` 收件人地址
- `$name` 设置昵称 可选参数

### 发送

#### 发送文本

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
$text = new \EasySwoole\Smtp\Request\Text();
$text->setSubject("Smtp Test Title");
$text->setBody("Smtp Test Body");

// 添加附件 可选
$text->addAttachment(__FILE__,'附件重命名');

// 发送
$mail->send($text);
```

#### 发送Html

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
$text = new \EasySwoole\Smtp\Request\Html();
$text->setSubject("Smtp Test Title");
$text->setBody("<h1>Smtp Test Body<h1>");

// 添加附件 可选
$text->addAttachment(__FILE__,'附件重命名');

// 发送
$mail->send($text);
```

### 注意事项

当开启异常的时候,发送过程中出现问题,将会抛出以下异常:

```php
try {
    /** @var \EasySwoole\Smtp\Mailer $mail **/
    $mail->send($text);
}catch (\EasySwoole\Smtp\Exception\Exception $exception) {

}
```

当未开启异常的时候,发送过程中出现问题,将会返回:

```php
/** @var \EasySwoole\Smtp\Mailer $mail **/
/** @var \EasySwoole\Smtp\Protocol\Response $response **/
$response = $mail->send($text);
```
