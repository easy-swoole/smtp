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

    const EOL = "\r\n";

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
            'package_max_length' => 1024 * 1024 * 2,
        ]);
    }

    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @param string $mailTo
     * @param MimeMessageBaseBean $mimeBean
     * @throws Exception
     */
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
        $boundary = '----=' . uniqid();
        //build body
        $mailBody = [];
        $mailBody[] = "MIME-Version: {$mimeBean->getMimeVersion()}";
        $mailBody[] = "From: {$this->createMailFrom()}";
        $mailBody[] = "To: {$mailTo}";
        $mailBody[] = "Subject: {$mimeBean->getSubject()}";
        $mailBody[] = "Content-Type:{$mimeBean->getContentType()};boundary='" . $boundary . "'" . self::EOL;
        $mailBody[] = "Content-Transfer-Encoding:{$mimeBean->getContentTransferEncoding()}";
        //发送附件
        if (!empty($mimeBean->getAttachment())) {
            $this->createAttachment($mimeBean);
        }
        //构造body
        $this->client->send($mailBody);
        $this->client->send(".\r\n");
        $this->recvCodeCheck('250');
        $this->client->send("quit\r\n");
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


    private function createAttachment(MimeMessageBaseBean $mimeBean)
    {
        $headers = [];
        $headers[] = $mimeBean->getBody() . self::EOL;
        /**@var Attach $attach */
        foreach ($mimeBean->getAttachment() as $attach) {

            $contentType = $attach->getContentType() ?? 'application/octet-stream';


            $headers[] = "Content-type: " . $contentType . ";name=\"=?" . $mimeBean->getCharset() . "?B?" . base64_encode(basename($attach->getFilename())) . '?="';

            $headers[] = "Content-disposition: attachment; name=\"=?" . $mimeBean->getCharset() . "?B?" . base64_encode(basename($attach->getFilename())) . '?="';

            $headers[] = 'Content-Transfer-Encoding: ' . preg_match('#(multipart|message)/#A', $contentType) ? '8bit' : 'base64' . self::EOL;

            $headers[] = $attach->getContent() . self::EOL;
        }
        $headers[] = "--" . $boundary . "--";

        return str_replace(self::EOL . '.', self::EOL . '..', trim(implode(self::EOL, $headers)));

    }

    private function formatMsg(string $msg)
    {
        return $msg . self::EOL;
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