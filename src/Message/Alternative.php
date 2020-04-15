<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2020/4/14
 * Time: 17:19
 */

namespace EasySwoole\Smtp\Message;


use EasySwoole\Smtp\AbstractInterface\BaseMessage;
use EasySwoole\Smtp\AbstractInterface\MessageInterface;

class Alternative extends BaseMessage implements MessageInterface
{
    private $text;
    private $html;

    use Attachment;

    public function __construct(string $body)
    {
        parent::__construct($body);
        $this->html = new Html($body);
        $this->text = new Text(strip_tags($body));
    }


    protected function getGeneralBody()
    {
        $plain = $this->text->getBody();
        $html = $this->html->getBody();

        $body = array();
        $body[] = 'Content-Type: multipart/alternative; boundary="' . $this->boundary[0] . '"';
        $body[] = null;
        $body[] = '--' . $this->boundary[0];

        foreach ($plain as $line) {
            $body[] = $line;
        }

        $body[] = '--' . $this->boundary[0];

        foreach ($html as $line) {
            $body[] = $line;
        }

        $body[] = '--' . $this->boundary[0] . '--';
        $body[] = null;
        $body[] = null;

        return $body;
    }


}