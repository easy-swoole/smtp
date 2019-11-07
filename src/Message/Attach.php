<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-02-10
 * Time: 18:56
 */

namespace EasySwoole\Smtp\Message;


use EasySwoole\Smtp\Exception\FileNotFoundException;
use EasySwoole\Smtp\MessageHandler;

class Attach extends MimeMessageBaseBean
{
    protected $contentDisposition;

    /**
     * 添加附件
     * @param string $body
     * @return static
     * @throws FileNotFoundException
     */
    public static function create(string $body)
    {
        $obj = new static();
        $stream = $obj->readFile($body);
        $obj->setBody($stream);
        $contentType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $stream);
        if (!strcasecmp($contentType, 'message/rfc822')) { // not allowed for attached files
            $contentType = 'application/octet-stream';
        } elseif (!strcasecmp($contentType, 'image/svg')) { // Troublesome for some mailers...
            $contentType = 'image/svg+xml';
        }
        $obj->contentType = $contentType;
        $obj->contentTransferEncoding = preg_match('#(multipart|message)/#A', $obj->contentType) ? MessageHandler::ENCODING_8BIT : MessageHandler::ENCODING_BASE64;
        $obj->contentDisposition = 'attachment;filename="' . MessageHandler::fixEncoding(basename($body)) . '"';
        return $obj;
    }

    /**
     * 设置下载的文件头部信息
     * @param string $contentDisposition
     */
    public function setContentDisposition(string $contentDisposition): void
    {
        $this->contentDisposition = $contentDisposition;
    }

    /**
     * @return mixed
     */
    public function getContentDisposition()
    {
        return $this->contentDisposition;
    }

    /**
     * readFile
     * @param string $filePath
     * @return string
     * @throws FileNotFoundException
     */
    protected function readFile(string $filePath): string
    {
        /**
         * read bin
         */
        $handle = fopen($filePath, 'rb');
        $stream = fread($handle, filesize($filePath));
        fclose($handle);
        //$stream = @file_get_contents($filePath); // @ is escalated to exception
        if ($stream === false) {
            throw new FileNotFoundException("Unable to read file '$filePath'.");
        }
        return $stream;
    }
}