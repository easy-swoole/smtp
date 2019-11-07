<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-02-02
 * Time: 21:00
 */

namespace EasySwoole\Smtp\Message;


use EasySwoole\Smtp\MessageHandler;

class Text extends MimeMessageBaseBean
{
    protected function initialize(): void
    {
        parent::initialize();
        $this->contentType = $this->contentType ?? 'text/plain; charset=UTF-8';
    }

    public function setBody($body): void
    {
        parent::setBody($body);
        $this->contentTransferEncoding = MessageHandler::getContentTransferEncoding($body);
    }
}