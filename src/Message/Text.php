<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2020/4/14
 * Time: 16:29
 */

namespace EasySwoole\Smtp\Message;


use EasySwoole\Smtp\AbstractInterface\BaseMessage;
use EasySwoole\Smtp\AbstractInterface\MessageInterface;

class Text extends BaseMessage implements MessageInterface
{
    use Attachment;

    protected function getGeneralBody()
    {
        $charset = $this->isUtf8($this->body) ? 'utf-8' : 'US-ASCII';
        $plane = str_replace("\r", '', trim($this->body));
        $count = ceil(strlen($plane) / $this->lineLength);

        $body = [];
        $body[] = 'Content-Type: text/plain; charset=' . $charset;
        $body[] = 'Content-Transfer-Encoding: 7bit';
        $body[] = null;

        for ($i = 0; $i < $count; $i++) {
            $body[] = substr($plane, ($i * $this->lineLength), $this->lineLength);
        }

        $body[] = null;
        $body[] = null;

        return $body;
    }
}