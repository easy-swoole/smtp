<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2020/4/14
 * Time: 10:10
 */

namespace EasySwoole\Smtp;


use EasySwoole\Spl\SplBean;

class Config extends SplBean
{
    protected $server;
    protected $port;
    protected $ssl;

    protected $username;
    protected $password;
    protected $mailFrom;

    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param mixed $server
     */
    public function setServer($server): void
    {
        $this->server = $server;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port): void
    {
        $this->port = $port;
    }

    /**
     * @return mixed
     */
    public function getSsl()
    {
        return $this->ssl;
    }

    /**
     * @param mixed $ssl
     */
    public function setSsl($ssl): void
    {
        $this->ssl = $ssl;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getMailFrom()
    {
        return $this->mailFrom;
    }

    /**
     * @param mixed $mailFrom
     */
    public function setMailFrom($mailFrom): void
    {
        $this->mailFrom = $mailFrom;
    }


    protected function initialize(): void
    {
        //默认关闭ssl
        $this->ssl = $this->ssl ?? false;
        $this->port = $this->port ?? ($this->ssl ? 465 : 25);
    }
}