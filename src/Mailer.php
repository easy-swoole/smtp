<?php

namespace EasySwoole\Smtp;

use EasySwoole\Smtp\Contract\RequestInterface;
use EasySwoole\Smtp\Exception\Exception;
use EasySwoole\Smtp\Protocol\Command;
use EasySwoole\Smtp\Protocol\Response;
use Swoole\Coroutine\Client;

class Mailer
{
    protected $charset = "UTF-8";

    protected $host;

    protected $port = 465;

    protected $ssl = true;

    protected $username;

    protected $password;

    protected $timeout = 5;

    protected $maxPackage = 1024 * 1024 * 2;

    protected $enableException = false;

    protected $address = [];

    /**
     * @param bool $enableException
     */
    public function setEnableException(bool $enableException): void
    {
        $this->enableException = $enableException;
    }


    /**
     * @param string $charset
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host): void
    {
        $this->host = $host;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @param bool $ssl
     */
    public function setSsl(bool $ssl): void
    {
        $this->ssl = $ssl;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @param float|int $maxPackage
     */
    public function setMaxPackage($maxPackage): void
    {
        $this->maxPackage = $maxPackage;
    }

    /**
     * @param string $address
     */
    public function addAddress(string $address): void
    {
        $this->address[] = $address;
    }


    public function __construct(bool $enableException = false)
    {
        $this->enableException = $enableException;
    }

    public function send(RequestInterface $request)
    {
        $client = new Client($this->ssl ? SWOOLE_TCP | SWOOLE_SSL : SWOOLE_TCP);
        $client->set([
            'open_eof_check' => true,
            'package_eof' => "\r\n",
            'package_max_length' => $this->maxPackage,
        ]);

        if ($client->connect($this->host, $this->port, $this->timeout) === false) {
            return $this->buildResponse(-1);
        }

        if (($ret = $this->recvCheck($client, 220)) === false) {
            return $this->buildResponse(-1);
        }

        $client->send(Command::ehlo(explode(' ', $ret)[1]));
        if (($ret = $this->recvCheck($client, 250)) === false) {
            return $this->buildResponse(-1);
        }

        while (1) {
            $peek = $client->recv($this->timeout);
            if (empty($peek)) {
                return $this->buildResponse(-1);
            } else {
                if (substr($peek, 3, 1) != '-') {
                    break;
                }
            }
        }

        $client->send(Command::auth('login'));
        if (($recv = $this->recvCheck($client, 334)) === false) {
            return $this->buildResponse(-1);
        }


        $client->send(Command::raw(base64_encode($this->username)));
        if (($recv = $this->recvCheck($client, 334)) === false) {
            return $this->buildResponse(-1);
        }


        $client->send(Command::raw(base64_encode($this->password)));
        if (($recv = $this->recvCheck($client, 235)) === false) {
            return $this->buildResponse(-1);
        }

        $client->send(Command::mail($this->username));
        if (($recv = $this->recvCheck($client, 250)) === false) {
            return $this->buildResponse(-1);
        }

        foreach ($this->address as $address) {
            $client->send(Command::rcpt($address));
            if (($recv = $this->recvCheck($client, 250)) === false) {
                return $this->buildResponse(-1);
            }
        }

        $client->send(Command::dataStart());
        if (($recv = $this->recvCheck($client, 354)) === false) {
            return $this->buildResponse(-1);
        }


        $body = "Hello,this is smtp test!";
        $mailTo = implode(",", $this->address);
        //build body
        $mailBody = [];
        $mailBody[] = "MIME-Version: 1.0";
        $mailBody[] = "From: {$this->username}<{$this->username}>";
        $mailBody[] = "To: {$mailTo}";
        $mailBody[] = "Subject: =?{$this->charset}?B?" . base64_encode($body) . "?=";

        $mailBody[] = "Content-Type: text/plain; charset=UTF-8;";
        $encoding = preg_match('#[^\n]{990}#', $body)
            ? 'quoted-printable'
            : (preg_match('#[\x80-\xFF]#', $body) ? '8bit' : '7bit');
        $mailBody[] = Command::raw("Content-Transfer-Encoding: $encoding");

        $mailBody[] = Command::raw($body);


        $client->send(implode("\r\n", $mailBody));
        $client->send(Command::dataEnd());
        if (($recv = $this->recvCheck($client, 250)) === false) {
            return $this->buildResponse(-1);
        }

        $client->send(Command::quit());
        if (($recv = $this->recvCheck($client, 221)) === false) {
            return $this->buildResponse(-1);
        }

    }

    protected function recvCheck(Client $client, string $code)
    {
        $recv = $client->recv($this->timeout);
        if ($recv && strpos($recv, $code) !== false) {
            var_dump($recv);
            return $recv;
        }

        var_dump($recv);

        return false;
    }

    protected function buildResponse($status): Response
    {
        if ($this->enableException === true) {
            throw new Exception();
        }

        return new Response();
    }
}