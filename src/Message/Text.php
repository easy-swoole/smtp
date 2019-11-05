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

    public function setBody($body): void
    {
        //设置content-transfer-encode
        $this->setContentTransferEncoding(preg_match('#[^\n]{990}#', $body)
            ? self::ENCODING_QUOTED_PRINTABLE
            : (preg_match('#[\x80-\xFF]#', $body) ? self::ENCODING_8BIT : self::ENCODING_7BIT));
        parent::setBody($body);
    }
}