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
        $client->send($mailTo, $mimeBean);
        return true;
    }


}