<?php
/**
 * Created by PhpStorm.
 * User: runs
 * Date: 19-1-28
 * Time: 下午3:46
 */

namespace EasySwoole\Smtp;


use EasySwoole\Smtp\Exception\Exception;
use EasySwoole\Smtp\Message\Attach;
use EasySwoole\Smtp\Message\MimeMessageBaseBean;
use Swoole\Coroutine\Client;

class MailerClient
{
    /** @var Client|null */
    private $client;

    private $timeout = 3.0;

    private $config;


    public function __construct(MailerConfig $config)
    {
        $this->config = $config;
        if ($config->isSsl()) {
            $this->client = new Client(SWOOLE_TCP | SWOOLE_SSL);
        } else {
            $this->client = new Client(SWOOLE_TCP);
        }

        $this->client->set([
            'open_eof_check' => true,
            'package_eof' => "\r\n",
            'package_max_length' => $this->config->getMaxPackage(),
        ]);

        $this->timeout = $this->config->getTimeout();
    }

    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    //发送操作
    public function send(string $mailTo, MimeMessageBaseBean $mimeBean)
    {
        /*
         * 发送ehlo
         */
        if ($this->client->connect($this->config->getServer(), $this->config->getPort(), $this->timeout) === false) {
            throw new Exception("connect {$this->config->getServer()}@{$this->config->getPort()} fail");
        }
        $str = $this->recvCodeCheck('220');
        $ehloHost = explode(' ', $str)[1];
        $this->client->send($this->formatMsg("ehlo {$ehloHost}"));
        //先看是否得到250应答,并清除多余应答
        $this->recvCodeCheck('250');
        while (1) {
            $peek = $this->client->recv($this->timeout);
            if (empty($peek)) {
                throw new Exception('waiting 250 code error');
            } else {
                if (substr($peek, 3, 1) != '-') {
                    break;
                }
            }
        }
        $this->client->send($this->formatMsg("auth login"));
        $this->recvCodeCheck('334');
        $this->client->send($this->formatMsg(base64_encode($this->config->getUsername())));
        $this->recvCodeCheck('334');
        $this->client->send($this->formatMsg(base64_encode($this->config->getPassword())));
        $this->recvCodeCheck('235');
        //start send data
        $this->client->send($this->formatMsg("mail from:<{$this->config->getMailFrom()}>"));
        $this->recvCodeCheck('250');
        $this->client->send($this->formatMsg("rcpt to:<{$mailTo}>"));
        $this->recvCodeCheck('250');
        $this->client->send($this->formatMsg("data"));
        $this->recvCodeCheck('354');

        //build body
        $mailBody = [];
        $mailBody[] = "MIME-Version: {$mimeBean->getMimeVersion()}";
        $mailBody[] = "From: {$this->createMailFrom()}";
        $mailBody[] = "To: {$mailTo}";
        $mailBody[] = "Subject: =?{$mimeBean->getCharset()}?B?" . base64_encode($mimeBean->getSubject()) . "?=";

        $boundary = '------' . uniqid();
        if (!empty($mimeBean->getAttachments())) {
            $mailBody[] = $this->formatMsg("Content-Type: multipart/mixed;boundary=\"" . $boundary . "\"");
            $mailBody[] = "--" . $boundary;
        }
        $mailBody[] = "Content-Type: {$mimeBean->getContentType()}";
        $mailBody[] = $this->formatMsg("Content-Transfer-Encoding: {$mimeBean->getContentTransferEncoding()}");

        $mailBody[] = $this->formatMsg(MessageHandler::getEncodingContent($mimeBean));
        //发送附件
        if (!empty($mimeBean->getAttachments())) {
            foreach ($mimeBean->getAttachments() as $attach) {
                $mailBody[] = "--" . $boundary;
                $mailBody = array_merge($mailBody, $this->createAttachment($attach));
            }
            $mailBody[] = "--" . $boundary . "--";
        }

        $output = preg_replace('#^\.#m', '..', trim(implode(MessageHandler::EOL, $mailBody)));

        //构造body
        $this->client->send($this->formatMsg($output));
        $this->client->send($this->formatMsg("."));
        $this->recvCodeCheck('250');
        $this->client->send($this->formatMsg("quit"));
        $this->recvCodeCheck('221');
    }

    /**
     * @param string $string
     * @return string
     * @throws Exception
     */
    private function recvCodeCheck(string $string): string
    {
        $recv = $this->client->recv($this->timeout);
        if ($recv === false) {
            throw new Exception("expect code {$string} timeout");
        }
        if ($recv && strpos($recv, $string) !== false) {
            return $recv;
        } else {
            throw new Exception($recv);
        }
    }

    /**
     * createMailFrom
     *
     * @return string
     */
    private function createMailFrom(): string
    {
        if ($this->config->getMailFrom()) {
            return "{$this->config->getMailFrom()} <{$this->config->getUsername()}>";
        }
        return $this->config->getUsername();
    }

    //创建附件
    private function createAttachment(Attach $attach)
    {
        $attachmentBody = [];

        $attachmentBody[] = "Content-Type:" . $attach->getContentType();

        $attachmentBody[] = 'Content-Transfer-Encoding: ' . $attach->getContentTransferEncoding();

        $attachmentBody[] = $this->formatMsg("Content-Disposition:" . $attach->getContentDisposition());

        $attachmentBody[] = $this->formatMsg(MessageHandler::getEncodingContent($attach));

        return $attachmentBody;

    }

    /**
     * 追加结束符
     * @param string $msg
     * @return string
     */
    private function formatMsg(string $msg)
    {
        return $msg . MessageHandler::EOL;
    }

    /**
     * close
     */
    public function close(): void
    {
        if ($this->client->connected) {
            $this->client->close();
        }
    }


    /**
     * __destruct
     */
    public function __destruct()
    {
        $this->close();
    }
}