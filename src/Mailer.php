<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2020/4/14
 * Time: 10:09
 */

namespace EasySwoole\Smtp;

use EasySwoole\Smtp\AbstractInterface\MessageInterface;
use EasySwoole\Smtp\Exception\ConnectException;
use EasySwoole\Smtp\Exception\Exception;
use EasySwoole\Smtp\Network\Config as ClientConfig;
use EasySwoole\Smtp\Network\Client;
use EasySwoole\Utility\Str;

class Mailer
{
    private $headers = [];
    private $subject;
    private $bcc = [];
    private $cc = [];
    private $to = [];

    private $config;
    private $client;
    private $clientConfig;

    public function __construct(Config $config, ?ClientConfig $clientConfig = null)
    {
        $this->config = $config;
        $this->clientConfig = $clientConfig;
    }


    public function addBcc(string $email, ?string $name = null)
    {
        $this->bcc[$email] = $name;
        return $this;
    }


    public function addCc(string $email, ?string $name = null)
    {
        $this->cc[$email] = $name;
        return $this;
    }


    public function addTo(string $email, ?string $name = null)
    {
        $this->to[$email] = $name;
        return $this;
    }


    public function setSubject(string $subject)
    {
        $this->subject = $subject;
        return $this;
    }


    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }


    public function send(MessageInterface $message)
    {
        $this->connect();
        $this->call("mail from:<{$this->config->getMailFrom()}>", 250);
        foreach ($this->to as $email => $name) {
            $this->call("rcpt to:<$email>", 250);
        }
        foreach ($this->bcc as $email => $name) {
            $this->call("rcpt to:<$email>", 250);
        }
        foreach ($this->cc as $email => $name) {
            $this->call("rcpt to:<$email>", 250);
        }
        $this->call("data", 354);

        foreach ($this->getHeaders() as $name => $value) {
            $this->getClient()->send($name . ':' . $value);
        }
        foreach ($message->getBody() as $line) {
            if (strpos($line, '.') === 0) {
                $line = '.' . $line;
            }
            $this->getClient()->send($line);
        }
        $this->call('.', 250);//告诉服务器发送完成
        $this->getClient()->send('QUIT');
        $this->getClient()->close();
    }


    private function getClient(): Client
    {
        if (!($this->client instanceof Client)) {
            if (!($this->clientConfig instanceof ClientConfig)) {
                $this->clientConfig = new ClientConfig();
            }
            $this->client = new Client($this->config->getSsl() ? SWOOLE_TCP | SWOOLE_SSL : SWOOLE_TCP, $this->clientConfig);
        }
        return $this->client;
    }


    private function connect()
    {
        if (!$this->getClient()->connect($this->config->getServer(), $this->config->getPort())) {
            $this->getClient()->close();
            throw new ConnectException("connect {$this->config->getServer()}@{$this->config->getPort()} fail");
        }
        if (!Str::contains($receive = $this->getClient()->receive(), 220)) {
            $this->getClient()->close();
            throw new ConnectException("connect {$this->config->getServer()}@{$this->config->getPort()} fail");
        }
        $this->callEhlo(explode(' ', $receive)[1]);
        $this->call('auth login', 334);
        $this->call(base64_encode($this->config->getUsername()), 334);
        $this->call(base64_encode($this->config->getPassword()), 235);
    }


    private function call(string $command, int $code)
    {
        $this->getClient()->send($command);
        $receive = $this->getClient()->receive();
        if (!Str::contains($receive, $code)) {
            $this->getClient()->close();
            throw new Exception("except code {$code} fail");
        }
    }


    private function callEhlo(string $host)
    {
        $this->getClient()->send("ehlo {$host}");
        $receive = $this->getClient()->receive();
        if (!Str::contains($receive, 250)) {
            throw new Exception("connect mail server:{$host} fail");
        }
        //清除多余回应
        while (1) {
            $receive = $this->getClient()->receive();
            if (substr($receive, 3, 1) !== '-') {
                break;
            }
        }
    }


    private function getHeaders()
    {
        $timestamp = $this->getTimestamp();

        $subject = trim($this->subject);
        $subject = str_replace(array("\n", "\r"), '', $subject);

        $to = $cc = $bcc = array();
        foreach ($this->to as $email => $name) {
            $to[] = trim($name . ' <' . $email . '>');
        }

        foreach ($this->cc as $email => $name) {
            $cc[] = trim($name . ' <' . $email . '>');
        }

        foreach ($this->bcc as $email => $name) {
            $bcc[] = trim($name . ' <' . $email . '>');
        }

        $headers = array(
            'Date' => $timestamp,
            'Subject' => $subject,
            'From' => '<' . $this->config->getUsername() . '>',
            'To' => implode(', ', $to));

        if (!empty($cc)) {
            $headers['Cc'] = implode(', ', $cc);
        }

        if (!empty($bcc)) {
            $headers['Bcc'] = implode(', ', $bcc);
        }

        $headers['Thread-Topic'] = $this->subject;

        $headers['Reply-To'] = '<' . $this->config->getUsername() . '>';

        foreach ($this->headers as $key => $value) {
            $headers[$key] = $value;
        }

        return $headers;
    }


    private function getTimestamp()
    {
        $zone = date('Z');
        $sign = ($zone < 0) ? '-' : '+';
        $zone = abs($zone);
        $zone = (int)($zone / 3600) * 100 + ($zone % 3600) / 60;
        return sprintf("%s %s%04d", date('D, j M Y H:i:s'), $sign, $zone);
    }
}