<?php
/**
 * Created by PhpStorm.
 * User: runs
 * Date: 19-1-28
 * Time: 下午2:36
 */

namespace EasySwoole\Smtp;


use EasySwoole\Spl\SplBean;

/**
 * Class MailerConfig
 *
 * @package EasySwoole\Mailer
 */
class MailerConfig extends SplBean
{
    /** @var string 邮件服务器 */
    protected $server;

    /** @var int 端口号 */
    protected $port;

    /** @var bool 是否开启ssl */
    protected $ssl;

    /** @var bool 是否开启startSSL */
    protected $startSSL;

    /** @var string 登陆用户名 */
    protected $username;

    /** @var string 登陆密码 OR 授权码 */
    protected $password;

    /** @var string 发件人 */
    protected $mailFrom;

    /**
     * initialize
     * 初始化操作
     */
    protected function initialize(): void
    {
        /** @var bool ssl 默认关闭ssl */
        $this->ssl = $this->ssl ?? false;
        /** @var bool startSSL 默认关闭startSSL */
        $this->startSSL = $this->startSSL ?? false;
        /** @var int port 尝试自动识别端口号 */
        $this->port = $this->port ?? ($this->ssl ? 465 : ($this->startSSL ? 587 : 25));
    }

    /**
     * @return string
     */
    public function getServer(): string
    {
        return $this->server;
    }

    /**
     * @param string $server
     */
    public function setServer(string $server): void
    {
        $this->server = $server;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return bool
     */
    public function isSsl(): bool
    {
        return $this->ssl;
    }

    /**
     * @param bool $ssl
     */
    public function setSsl(bool $ssl): void
    {
        $this->ssl = $ssl;
    }

    /**
     * @return bool
     */
    public function isStartSSL(): bool
    {
        return $this->startSSL;
    }

    /**
     * @param bool $startSSL
     */
    public function setStartSSL(bool $startSSL): void
    {
        $this->startSSL = $startSSL;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getMailFrom(): string
    {
        return $this->mailFrom;
    }

    /**
     * @param string $mailFrom
     */
    public function setMailFrom(string $mailFrom): void
    {
        $this->mailFrom = $mailFrom;
    }
}