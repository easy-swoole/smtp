<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2020/4/14
 * Time: 10:50
 */

namespace EasySwoole\Smtp\Network;


class Client
{
    private $config;
    private $client;

    public function __construct($type, ?Config $config = null)
    {
        if (!($config instanceof Config)) {
            $config = new Config();
        }
        $this->client = new \Swoole\Coroutine\Client($type);
        $this->client->set([
            'open_eof_check' => true,
            'package_eof' => "\r\n",
            'package_max_length' => $config->getMaxPackage()
        ]);
        $this->config = $config;
    }


    public function connect(string $host, int $port)
    {
        return $this->client->connect($host, $port, $this->config->getTimeout());
    }


    public function send(?string $command)
    {
        $this->client->send($command . "\r\n");
    }


    public function receive()
    {
        return $this->client->recv($this->config->getTimeout());
    }


    public function close()
    {
        $this->client->close();
    }
}