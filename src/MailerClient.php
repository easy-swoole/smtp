<?php
/**
 * Created by PhpStorm.
 * User: runs
 * Date: 19-1-28
 * Time: 下午3:46
 */

namespace EasySwoole\Smtp;


use EasySwoole\Smtp\Exception\Exception;
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
        if($config->isSsl()){
            $this->client = new Client( SWOOLE_TCP | SWOOLE_SSL);
        }else{
            $this->client = new Client( SWOOLE_TCP );
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
     * @param string              $mailTo
     * @param MimeMessageBaseBean $mimeBean
     * @throws Exception
     */
    public function send(string $mailTo, MimeMessageBaseBean $mimeBean)
    {
        /*
         * 发送ehlo
         */
        if ($this->client->connect($this->config->getServer(), $this->config->getPort(),$this->timeout) === false) {
            throw new Exception("connect {$this->config->getServer()}@{$this->config->getPort()} fail");
        }
        $str = $this->recvCodeCheck('220');
        $ehloHost = explode(' ',$str)[1];
        $this->client->send("ehlo {$ehloHost}\r\n");
        //先看是否得到250应答,并清除多余应答
        $this->recvCodeCheck('250');
        while (1){
            $peek = $this->client->recv($this->timeout);
            if(empty($peek)){
                throw new Exception('waiting 250 code error');
            }else{
                if(substr($peek,3,1) != '-'){
                    break;
                }
            }
        }
        $this->client->send("auth login\r\n");
        $this->recvCodeCheck('334');
        $this->client->send(base64_encode($this->config->getUsername())."\r\n");
        $this->recvCodeCheck('334');
        $this->client->send(base64_encode($this->config->getPassword())."\r\n");
        $this->recvCodeCheck('235');
        //start send data
        $this->client->send("mail from:<{$this->config->getMailFrom()}>\r\n");
        $this->recvCodeCheck('250');
        $this->client->send("rcpt to:<{$mailTo}>\r\n");
        $this->recvCodeCheck('250');
        $this->client->send("data\r\n");
        $this->recvCodeCheck('354');
        //build body
        $mail = "MIME-Version: {$mimeBean->getMimeVersion()}\r\n";
        $mail.= "From: {$this->createMailFrom()}\r\n";
        $mail.= "To: {$mailTo}\r\n";
        $mail.= "Subject: {$mimeBean->getSubject()}\r\n";
        //构造body
        $this->client->send($mail);
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
    private function recvCodeCheck(string $string) : string
    {
        $recv = $this->client->recv($this->timeout);
        if ($recv === false) {
            throw new Exception("expect code {$string} timeout");
        }
        if ($recv && strpos($recv, $string) !== false) {
            return $recv;
        }else{
            throw new Exception($recv);
        }
    }

    /**
     * createMailFrom
     *
     * @return string
     */
    private function createMailFrom() : string
    {
        if ($this->config->getMailFrom()) {
            return "{$this->config->getMailFrom()} <{$this->config->getUsername()}>";
        }
        return $this->config->getUsername();
    }

    /**
     * close
     */
    public function close() : void
    {
        if ($this->client->connected)
        {
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