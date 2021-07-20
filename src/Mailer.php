<?php

namespace EasySwoole\Smtp;

use EasySwoole\Smtp\Contract\RequestInterface;
use EasySwoole\Smtp\Exception\Exception;
use EasySwoole\Smtp\Protocol\Command;
use EasySwoole\Smtp\Protocol\Response;
use Swoole\Coroutine\Client;

class Mailer
{
    protected $host;

    protected $port = 465;

    protected $ssl = true;

    protected $username;

    protected $password;

    protected $timeout = 5;

    protected $maxPackage = 1024 * 1024 * 2;

    protected $enableException = false;

    protected $to = [];

    protected $from = [];

    protected $replyTo = [];

    protected $cc = [];

    protected $bcc = [];

    /**
     * @param bool $enableException
     */
    public function setEnableException(bool $enableException): void
    {
        $this->enableException = $enableException;
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
     * @param string $name
     */
    public function addAddress(string $address, string $name = ''): void
    {
        $this->to[] = [$address, $name];
    }

    /**
     * @param string $address
     * @param string $name
     */
    public function setFrom(string $address, string $name = ''): void
    {
        $this->from = [$address, $name];
    }

    /**
     * @param $address
     * @param $name
     */
    public function setReplyTo(string $address, string $name = ""): void
    {
        $this->replyTo = [$address, $name];
    }


    /**
     * @param string $address
     * @param string $name
     */
    public function addCc(string $address, string $name = ''): void
    {
        $this->cc[] = [$address, $name];
    }

    /**
     * @param string $address
     * @param string $name
     */
    public function addBcc(string $address, string $name = ''): void
    {
        $this->bcc[] = [$address, $name];
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

        foreach (array_merge($this->to, $this->cc, $this->bcc) as $item) {
            $client->send(Command::rcpt($item[0]));
            if (($recv = $this->recvCheck($client, 250)) === false) {
                return $this->buildResponse(-1);
            }
        }

        $client->send(Command::dataStart());
        if (($recv = $this->recvCheck($client, 354)) === false) {
            return $this->buildResponse(-1);
        }

        $client->send($this->__createHeader() . $request->getPayload());
        $client->send(Command::dataEnd());
        if (($recv = $this->recvCheck($client, 250)) === false) {
            return $this->buildResponse(-1);
        }

        $client->send(Command::quit());
        if (($recv = $this->recvCheck($client, 221)) === false) {
            return $this->buildResponse(-1);
        }

    }

    private function __addrFormat(array $addr)
    {
        $ret = [];
        foreach ($addr as $item) {
            $address = current($item);
            $name = next($item);
            reset($item);

            if ($name) {
                $ret[] = "{$name} <$address>";
                continue;
            }

            $ret[] = "$address";
        }

        return $ret;
    }

    private function __createHeader()
    {
        $header = [];
        $header[] = 'From: ' . ($this->from ? current($this->__addrFormat([$this->from])) : "$this->username");
        $header[] = 'To: ' . ($this->to ? implode(',', $this->__addrFormat($this->to)) : 'undisclosed-recipients:;');

        if ($this->cc) {
            $header[] = 'Cc: ' . implode(',', $this->__addrFormat($this->cc));
        }

        if ($this->replyTo) {
            $header[] = 'Reply-To: ' . current($this->__addrFormat([$this->replyTo]));
        }

        return implode("\r\n", $header) . "\r\n";
    }

    protected function recvCheck(Client $client, string $code)
    {
        $recv = $client->recv($this->timeout);
        if ($recv && strpos($recv, $code) !== false) {
            return $recv;
        }

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