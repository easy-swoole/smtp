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
    protected $port = 25;

    /** @var bool 是否开启ssl */
    protected $ssl;

    /** @var string 登陆用户名 */
    protected $username;

    /** @var string 登陆密码 OR 授权码 */
    protected $password;

    /** @var string 发件人 */
    protected $mailFrom;

    /**
     * @var float 发送超时时间
     */
    protected $timeout = 3.0;

    /**
     * @var int 最大包大小
     */
    protected $maxPackage = 1024 * 1024 * 2;//2M

    /**
     * initialize
     * 初始化操作
     */
    protected function initialize(): void
    {
        /** @var bool ssl 默认关闭ssl */
        $this->ssl = $this->ssl ?? false;
        /** @var int port 尝试自动识别端口号 */
        $this->port = $this->port ?? ($this->ssl ? 465 : 25);
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
        if (empty($this->mailFrom)) {
            $this->mailFrom = $username;
        }
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

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function setMaxPackage(int $maxPackage)
    {
        $this->maxPackage = $maxPackage;
    }

    public function getMaxPackage()
    {
        return $this->maxPackage;
    }
}