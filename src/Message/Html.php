<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2020/4/14
 * Time: 16:30
 */

namespace EasySwoole\Smtp\Message;


use EasySwoole\Smtp\AbstractInterface\BaseMessage;
use EasySwoole\Smtp\AbstractInterface\MessageInterface;

class Html extends BaseMessage implements MessageInterface
{
    use Attachment;

    protected function getGeneralBody()
    {
        $charset = $this->isUtf8($this->body) ? 'utf-8' : 'US-ASCII';
        $html = str_replace("\r", '', trim($this->body));

        $encoded = explode("\n", $this->quotedPrintableEncode($html));
        $body = array();
        $body[] = 'Content-Type: text/html; charset=' . $charset;
        $body[] = 'Content-Transfer-Encoding: quoted-printable' . "\n";

        foreach ($encoded as $line) {
            $body[] = $line;
        }

        $body[] = null;
        $body[] = null;

        return $body;
    }
}