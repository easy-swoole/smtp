<?php
/**
 * Created by PhpStorm.
 * User: runs
 * Date: 19-1-28
 * Time: 下午5:43
 */

namespace EasySwoole\Smtp\Message;

use EasySwoole\Spl\SplBean;

abstract class MimeMessageBaseBean extends SplBean
{

    protected $mimeVersion;              //协议版本
    protected $contentType;              //设置contentType
    protected $charset;                  //设置字符
    protected $contentTransferEncoding;  //设置编码
    protected $subject;
    protected $body;
    protected $attachments = [];

    protected function initialize(): void
    {
        $this->mimeVersion = $this->mimeVersion ?? '1.0';
        $this->charset = $this->charset ?? 'UTF-8';
    }

    /**
     * @return mixed
     */
    public function getMimeVersion()
    {
        return $this->mimeVersion;
    }

    /**
     * @param mixed $mimeVersion
     */
    public function setMimeVersion($mimeVersion): void
    {
        $this->mimeVersion = $mimeVersion;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @return mixed
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param mixed $charset
     */
    public function setCharset($charset): void
    {
        $this->charset = $charset;
    }

    /**
     * @return mixed
     */
    public function getContentTransferEncoding()
    {
        return $this->contentTransferEncoding;
    }

    /**
     * @param mixed $contentTransferEncoding
     */
    public function setContentTransferEncoding($contentTransferEncoding): void
    {
        $this->contentTransferEncoding = $contentTransferEncoding;
    }


    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /**
     * @param $attachment
     */
    public function addAttachment($attachment)
    {
        $this->attachments[] = $attachment;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }
}