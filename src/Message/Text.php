<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-02-02
 * Time: 21:00
 */

namespace EasySwoole\Smtp\Message;


class Text extends MimeMessageBaseBean
{
    protected function initialize(): void
    {
        parent::initialize();
        $this->contentType = $this->contentType ?? 'text/plain';
    }
}