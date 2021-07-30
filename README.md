# SMTP

## Introduction

E-mail is a communication method that provides information exchange by electronic means. It is the most widely used service on the Internet. E-mail is indispensable to almost every web application, whether it is a newsletter or order confirmation. The library send e-mail with the swoole coroutine client.

## Component requirements

- php: >=7.1.0
- ext-swoole: ^4.2.6
- easyswoole/spl: ^1.1
- easyswoole/utility: ^1.0

## Installation

```php
composer require easyswoole/smtp=1.x
```

## Instruction for use

### Configure mail component

#### Set configuration

Set the host of the SMTP server you want to connect to

```php
public function setServer(string $server): void
```

Set the port of the SMTP server you want to connect to

```php
public function setPort(int $port): void
```

Set support SSL

```php
public function setSsl(bool $ssl): void
```

Set SMTP username

```php
public function setUsername(string $username): void
```

Set SMTP password

```php
public function setPassword(string $password): void
```

Set mail sender

```php
public function setMailFrom(string $mailFrom): void
```

Set timeout

```php
public function setTimeout(float $timeout): void
```

Set the size of the mail that is allowed to be sent

```php
public function setTimeout(float $timeout): void
```

#### Get configuration

Get the host of the SMTP server you want to connect to

```php
public function getServer(): string
```

Get the port of the SMTP server you want to connect to

```php
public function getPort(): int
```

Get whether SSL is supported

```php
public function isSsl(): bool
```

Get SMTP username

```php
public function getUsername(): string
```

Get SMTP password

```php
public function getPassword(): string
```

Get mail sender

```php
public function getMailFrom(): string
```

Get timeout

```php
public function getTimeout(): float
```

Get the size of the mail that is allowed to be sent

```php
public function getMaxPackage()
```

### Configure for mail content

#### Set config for mail Content

Set protocol version

```php
public function setMimeVersion($mimeVersion): void
```

Set the content type used for the mail content

```php
public function setContentType($contentType): void
```

Set the character set used for the mail content

```php
public function setCharset($charset): void
```

Set the file encoding used for the mail content

```php
public function setContentTransferEncoding($contentTransferEncoding): void
```

Set the subject of the mail content

```php
public function setSubject($subject): void
```

Set the body of the mail content

```php
public function setBody($body): void
```

Add attachment in the mail content

```php
public function addAttachment($attachment)
```


#### Get config for mail Content

Get protocol version

```php
public function getMimeVersion()
```

Get the content type used for the mail content

```php
public function getContentType()
```

Get the character set used for the mail content

```php
public function getCharset()
```

Get the file encoding used for the mail content

```php
public function getContentTransferEncoding()
```

Get the subject of the mail content

```php
public function getSubject()
```

Get the body of the mail content

```php
public function getBody()
```

Add attachment in the mail content

```php
public function getAttachments()
```

## A Simple Example For Use

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use EasySwoole\Smtp\Mailer;
use EasySwoole\Smtp\MailerConfig;
use EasySwoole\Smtp\Message\Html;
use EasySwoole\Smtp\Message\Attach;

// Because the smtp component is a coroutine client component, it must be called in the coroutine environment.
// Therefore, the function "go" is used here to create a coroutine environment for use.
go(function () {
    $config = new MailerConfig();
    $config->setServer('smtp.163.com');
    $config->setSsl(false);
    $config->setUsername('huizhang');
    $config->setPassword('*******');
    $config->setMailFrom('xx@163.com');
    $config->setTimeout(10); // Set connection timeout of the client
    $config->setMaxPackage(1024 * 1024 * 5); // Set the size of the packet to be sent

    // Set the content type of the mail content to text or HTML format
    $mimeBean = new Html();
    $mimeBean->setSubject('Hello Word!');
    $mimeBean->setBody('<h1>Hello Word</h1>');

    // Add attachment in the mail content
    $mimeBean->addAttachment(Attach::create('./test.txt'));

    $mailer = new Mailer($config);
    $mailer->sendTo('xx@qq.com', $mimeBean);
});
```

## The Example For Advanced Use

At present, only Text and HTML are the supported type for the mail content.

### Text

Example for use:

```php
$mimeBean = new \EasySwoole\Smtp\Message\Text();
$mimeBean->setSubject('Hello Word!');
$mimeBean->setBody('<h1>Hello Word</h1>');
```

### HTML

Example for use:

```php
$mimeBean = new \EasySwoole\Smtp\Message\Html();
$mimeBean->setSubject('Hello Word!');
$mimeBean->setBody('<h1>Hello Word</h1>');
```
