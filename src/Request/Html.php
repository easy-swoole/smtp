<?php


namespace EasySwoole\Smtp\Request;


use EasySwoole\Smtp\ContentType;
use EasySwoole\Smtp\Protocol\Request;

class Html extends Request
{
    public function __construct()
    {
        $this->contentType = ContentType::CONTENT_TYPE_TEXT_HTML;
    }
}