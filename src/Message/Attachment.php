<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2020/4/14
 * Time: 16:30
 */

namespace EasySwoole\Smtp\Message;

use EasySwoole\Utility\MimeType;

trait Attachment
{
    private $attachments = [];

    public function addAttachment(string $filename, string $data, ?string $mime = null)
    {
        $this->attachments[] = [$filename, $data, $mime];
        return $this;
    }


    private function addAttachmentBody(array $body)
    {
        foreach ($this->attachments as $attachment) {
            list($name, $data, $mime) = $attachment;
            $mime = $mime ? $mime : MimeType::getMimeTypeFromFile($data);
            $content = base64_encode(file_get_contents($data));
            $count = ceil(strlen($content) / $this->lineLength);

            $body[] = '--' . $this->boundary[1];
            $body[] = 'Content-type: ' . $mime . '; name="' . $name . '"';
            $body[] = 'Content-disposition: attachment; filename="' . $name . '"';
            $body[] = 'Content-transfer-encoding: base64';
            $body[] = null;

            for ($i = 0; $i < $count; $i++) {
                $body[] = substr($content, ($i * $this->lineLength), $this->lineLength);
            }

            $body[] = null;
            $body[] = null;
        }

        $body[] = '--' . $this->boundary[1] . '--';

        return $body;
    }


    private function getAttachmentBody()
    {
        $generalBody = $this->getGeneralBody();
        $body = [];
        $body[] = 'Content-Type: multipart/mixed; boundary="' . $this->boundary[1] . '"';
        $body[] = null;
        $body[] = '--' . $this->boundary[1];

        foreach ($generalBody as $line) {
            $body[] = $line;
        }

        return $this->addAttachmentBody($body);
    }


    public function getBody()
    {
        return $this->attachments ? $this->getAttachmentBody() : $this->getGeneralBody();
    }
}