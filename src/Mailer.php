<?php
/**
 * Created by PhpStorm.
 * User: runs
 * Date: 19-1-10
 * Time: 上午4:00
 */

namespace EasySwoole\Smtp;


use EasySwoole\Smtp\Message\MimeMessageBaseBean;

class Mailer
{
    /** @var MailerConfig|null */
    private $config;

    /** @var MailerClient|null */
    private $client;

    /**
     * @param MailerConfig $config
     */
    public function __construct(MailerConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function connect() : bool
    {
        if ($this->config->isSsl()) {
            $this->client = new MailerClient($this->config->getServer(), $this->config->getPort(), SWOOLE_TCP | SWOOLE_SSL);
        } else {
            $this->client = new MailerClient($this->config->getServer(), $this->config->getPort(), SWOOLE_TCP);
        }

        $this->client->setTimeout($this->config->getTimeout());

        $this->client->sendHello();

        if ($this->config->isStartSSL()) {
            $this->client->enableSSL();
        }

        return true;
    }

    /**
     * mail
     *
     * @param string              $mailTo
     * @param MimeMessageBaseBean $mimeBean
     * @return bool
     * @throws \Exception
     */
    public function sendTo(string $mailTo, MimeMessageBaseBean $mimeBean)
    {
        /** 连接邮件服务器 */
        $this->connect();
        /** 身份鉴权 */
        if ($this->config->getUsername() !== null) {
            $this->client->auth($this->config->getUsername(), $this->config->getPassword());
        }
        /** 发送头信息 */
        $this->client->sendHeader($this->config->getUsername(), $mailTo);
        /** 发送主体 */
        $this->client->sendMime($this->createMailFrom(), $mailTo, $mimeBean);
        return true;
    }

    /**
     * createMailFrom
     *
     * @return string
     */
    protected function createMailFrom() : string
    {
        if ($this->config->getMailFrom()) {
            return "{$this->config->getMailFrom()} <{$this->config->getUsername()}>";
        }
        return $this->config->getUsername();
    }
}