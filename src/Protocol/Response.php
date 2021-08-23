<?php


namespace EasySwoole\Smtp\Protocol;


class Response
{
    const STATUS_OK = 0;

    const STATUS_CONNECT_TIMEOUT = 101;
    const STATUS_NOT_SMTP_PROTOCOL = 102;
    const STATUS_RECEIVE_TIMEOUT = 103;

    const STATUS_IDENTIFY_SENDER_ERROR = 201;
    const STATUS_AUTH_MODE_ERROR = 202;
    const STATUS_USERNAME_ERROR = 203;
    const STATUS_PASSWORD_ERROR = 204;
    const STATUS_FROM_MAIL_ERROR = 205;
    const STATUS_RCPT_MAIL_ERROR = 206;
    const STATUS_DATA_START_ERROR = 207;
    const STATUS_DATA_END_ERROR = 208;
    const STATUS_QUIT_ERROR = 209;


    /**
     * @var int $status
     */
    protected $status = self::STATUS_OK;

    /**
     * @var string $msg
     */
    protected $msg;

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

    /**
     * @param string $msg
     */
    public function setMsg(string $msg): void
    {
        $this->msg = $msg;
    }

    public static function status2msg(int $status)
    {
        switch ($status) {
            case self::STATUS_OK:
            {
                return 'ok.';
            }
            case self::STATUS_CONNECT_TIMEOUT:
            {
                return 'Client Connection timeout.';
            }
            case self::STATUS_RECEIVE_TIMEOUT:
            {
                return 'Client Receive timeout.';
            }
            case self::STATUS_NOT_SMTP_PROTOCOL:
            {
                return 'Non-smtp protocol.';
            }
            case self::STATUS_IDENTIFY_SENDER_ERROR:
            {
                return 'An error occurred identifying the sender to the smtp service.';
            }
            case self::STATUS_AUTH_MODE_ERROR:
            {
                return 'The authentication mode is incorrect.';
            }
            case self::STATUS_USERNAME_ERROR:
            {
                return 'User name error.';
            }
            case self::STATUS_PASSWORD_ERROR:
            {
                return 'Password error authentication failed.';
            }
            case self::STATUS_FROM_MAIL_ERROR:
            {
                return "The sender's email address is incorrect.";
            }
            case self::STATUS_RCPT_MAIL_ERROR:
            {
                return 'Recipient email address error.';
            }
            case self::STATUS_DATA_START_ERROR:
            {
                return 'An error occurred at the beginning of sending the actual data.';
            }
            case self::STATUS_DATA_END_ERROR:
            {
                return 'Error identifying the end of the data.';
            }
            case self::STATUS_QUIT_ERROR:
            {
                return 'Error exiting smtp session.';
            }
            default:
            {
                return 'Unknown error.';
            }
        }
    }
}