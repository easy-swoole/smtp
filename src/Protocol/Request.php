<?php


namespace EasySwoole\Smtp\Protocol;


use EasySwoole\Smtp\ContentType;
use EasySwoole\Smtp\Contract\RequestInterface;
use EasySwoole\Smtp\Exception\Exception;

abstract class Request implements RequestInterface
{
    protected $mineVersion = '1.0';
    protected $contentType;
    protected $charset = "UTF-8";
    protected $contentTransferEncoding;
    protected $subject;
    protected $body;

    /**
     * @param float $mineVersion
     */
    public function setMineVersion(float $mineVersion): void
    {
        $this->mineVersion = $mineVersion;
    }

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @param string $charset
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * @param mixed $contentTransferEncoding
     */
    public function setContentTransferEncoding($contentTransferEncoding): void
    {
        $this->contentTransferEncoding = $contentTransferEncoding;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /** @internal */
    public const EOL = "\r\n";


    protected $attachment;

    /**
     * @param string $path
     * @param string $name
     */
    public function addAttachment(string $path, string $name = ''): void
    {
        $this->attachment[] = [$path, $name];
    }

    public function getPayload()
    {
        $contentType = new ContentType($this->charset);
        $this->contentTransferEncoding = $contentType->getContentTransferEncoding($this->body);

        $payload = [];
        $payload[] = "MIME-Version: {$this->mineVersion}";
        $payload[] = "Subject: =?{$this->charset}?B?" . base64_encode($this->subject) . "?=";


        $boundary = uniqid();
        if ($this->attachment) {
            $payload[] = "Content-Type: multipart/mixed;boundary=\"" . $boundary . "\"" . self::EOL;
            $payload[] = "--" . $boundary;
        }


        $payload[] = "Content-Type: {$this->contentType}";
        $payload[] = "Content-Transfer-Encoding: {$this->contentTransferEncoding}" . self::EOL;
        $payload[] = $contentType->encodeString($this->body, $this->contentTransferEncoding) . self::EOL;

        if ($this->attachment) {
            foreach ($this->attachment as $item) {
                $payload[] = "--" . $boundary;
                $payload = array_merge($payload, $this->__createAttachment($contentType, $item));
            }
            $payload[] = "--" . $boundary . "--";
        }

        return preg_replace('#^\.#m', '..', trim(implode(self::EOL, $payload))) . self::EOL;
    }

    private function __createAttachment(ContentType $contentType, array $attachment)
    {
        $stream = $this->__readFile(current($attachment));
        $fileType = $contentType::filenameToType(current($attachment));
        $filename = $contentType->encodeHeader(next($attachment) ? current($attachment) : basename(prev($attachment)));
        $contentTransferEncoding = preg_match('#(multipart|message)/#A', $fileType) ? ContentType::ENCODING_8BIT : ContentType::ENCODING_BASE64;
        $contentDisposition = "attachment;filename=\"{$filename}\"";

        $attachmentBody = [];
        $attachmentBody[] = "Content-Type: " . $fileType;
        $attachmentBody[] = 'Content-Transfer-Encoding: ' . $contentTransferEncoding;
        $attachmentBody[] = "Content-Disposition: " . $contentDisposition . self::EOL;
        $attachmentBody[] = $contentType->encodeString($stream, $contentTransferEncoding) . self::EOL;

        return $attachmentBody;
    }

    private function __readFile(string $filePath): string
    {
        $stream = '';
        $handle = fopen($filePath, "rb");
        if ($handle) {
            while (!feof($handle))
                $stream .= fread($handle, 1024);
        }
        fclose($handle);
        if (!$stream) {
            throw new Exception("Unable to read file '$filePath'.");
        }
        return $stream;
    }
}