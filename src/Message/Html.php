<?php
/**
 * Created by PhpStorm.
 * User: runs
 * Date: 19-1-28
 * Time: 下午5:42
 */

namespace EasySwoole\Smtp\Message;


class Html extends MimeMessageBaseBean
{
    protected function initialize(): void
    {
        parent::initialize();
        $this->contentType = $this->contentType ?? 'text/html';
    }
}