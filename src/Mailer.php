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

    /**
     * @param MailerConfig $config
     */
    public function __construct(MailerConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param string              $mailTo
     * @param MimeMessageBaseBean $mimeBean
     * @return bool
     * @throws Exception\Exception
     */
    public function sendTo(string $mailTo, MimeMessageBaseBean $mimeBean)
    {
        $client = new MailerClient($this->config);
        //直接调用客户端
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
}