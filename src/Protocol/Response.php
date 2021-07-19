<?php


namespace EasySwoole\Smtp\Protocol;


class Response
{
    const STATUS_OK = 0;

    /**
     * @var int $status
     */
    protected $status = self::STATUS_OK;

    /**
     * @var string $message
     */
    protected $message = '';
}