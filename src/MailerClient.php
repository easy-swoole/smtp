<?php
/**
 * Created by PhpStorm.
 * User: runs
 * Date: 19-1-28
 * Time: 下午3:46
 */

namespace EasySwoole\Smtp;


use EasySwoole\Smtp\Message\MimeMessageBaseBean;
use Swoole\Coroutine\Client;

class MailerClient
{
    /** @var Client|null */
    private $client;

    /** @var string|null */
    private $serverHost;

    /**
     * @param string $server
     * @param int    $port
     * @param int    $type
     * @throws \Exception
     */
    public function __construct(string $server, int $port, $type)
    {
        $this->client = new Client($type);
        if ($this->client->connect($server, $port) === false) {
            throw new \Exception('client connect failure!');
        }

        if (!$this->recvHost()) {
            throw new \Exception('server not response!');
        }

        $this->setClientOption();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function sendHello() : bool
    {
        if (!$this->send('ehlo '. $this->serverHost)) {
            $this->close();
            throw new \Exception('send hello error!');
        }

        if (!$this->recvHas('250')) {
            $this->close();
            throw new \Exception('send hello error!');
        }
        return true;
    }

    /**
     * @param string $mailFrom
     * @param string $mailTo
     * @return bool
     * @throws \Exception
     */
    public function sendHeader(string $mailFrom, string $mailTo)
    {
        if (!$this->send("mail from:<{$mailFrom}>") || !$this->recvHas('250')) {
            $this->close();
            throw new \Exception('send mail from error!');
        }

        if (!$this->send("rcpt to:<{$mailTo}>") || !$this->recvHas('250')) {
            $this->close();
            throw new \Exception('send rcpt error!');
        }

        if (!$this->send('data') || !$this->recvHas('354')) {
            $this->close();
            throw new \Exception('send data error!');
        }

        return true;
    }

    /**
     * @param string              $mailFrom
     * @param string              $mailTo
     * @param MimeMessageBaseBean $mimeBean
     * @return bool
     * @throws \Exception
     */
    public function sendMime(string $mailFrom, string $mailTo, MimeMessageBaseBean $mimeBean)
    {
        $mail = "MIME-Version: {$mimeBean->getMimeVersion()}\r\n";
        $mail.= "From: {$mailFrom}\r\n";
        $mail.= "To: {$mailTo}\r\n";
        $mail.= "Subject: {$mimeBean->getSubject()}\r\n";
        $mail.= "Content-type: {$mimeBean->getContentType()}; charset={$mimeBean->getCharset()}\r\n";
        $mail.= "\r\n";
        $mail.= $mimeBean->getBody();

        $this->send($mail);
        if (!$this->send('.') || !$this->recvHas('250')) {
            $this->close();
            throw new \Exception('send mail error!');
        }

        if (!$this->send('quit') || !$this->recvHas('221')) {
            $this->close();
            throw new \Exception('send quit error!');
        }
        $this->close();
        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     * @throws \Exception
     */
    public function auth(string $username, string $password) : bool
    {
        if (!$this->send('auth login') || !$this->recvHas('334')) {
            $this->close();
            throw new \Exception('auth login error!');
        }

        if (!$this->send(base64_encode($username)) || !$this->recvHas('334')) {
            $this->close();
            throw new \Exception('auth username error!');
        }

        if (!$this->send(base64_encode($password)) || !$this->recvHas('235')) {
            $this->close();
            throw new \Exception('auth password error!');
        }

        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function enableSSL() : bool
    {
        if ($this->send('starttls') && $this->recvHas('220')) {
            $this->client->enableSSL();
            return $this->sendHello();
        }
        $this->close();
        throw new \Exception('enableSSL error!');
    }

    /**
     * @param string $msg
     * @return bool
     */
    private function send(string $msg) : bool
    {
        return $this->client->send("{$msg}\r\n");
    }

    /**
     * @param int $timeout
     * @return string|null
     */
    public function recv(int $timeout = -1) : ? string
    {
        $msg = $this->client->recv($timeout);
        if ($msg == '' || $msg === false)
        {
            return null;
        }
        return $msg;
    }

    /**
     * @param string $string
     * @return bool
     */
    private function recvHas(string $string) : bool
    {
        while (true) {
            $recv = $this->recv(1);
            if ($recv && strpos($recv, $string) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    private function recvHost() : bool
    {
        $recv = $this->recv();
        if (!$recv || strpos($recv, '220') === false) {
            return false;
        }

        $this->serverHost = (explode(' ', $recv))[1];
        return true;
    }

    /**
     * setClientOption
     */
    private function setClientOption()
    {
        $this->client->set([
            'open_eof_check' => true,
            'package_eof' => "\r\n",
            'package_max_length' => 1024 * 1024 * 2,
        ]);
    }

    /**
     * close
     */
    private function close() : void
    {
        if ($this->client->connected)
        {
            $this->client->close();
        }
    }
}