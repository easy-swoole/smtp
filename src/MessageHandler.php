<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2019/11/6
 * Time: 11:06
 */

namespace EasySwoole\Smtp;


use EasySwoole\Smtp\Exception\InvalidStateException;
use EasySwoole\Smtp\Message\MimeMessageBaseBean;

class MessageHandler
{
    public const
        ENCODING_BASE64 = 'base64',
        ENCODING_7BIT = '7bit',
        ENCODING_8BIT = '8bit',
        ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    /** @internal */
    public const EOL = "\r\n";

    public const LINE_LENGTH = 76;

    public static function getContentTransferEncoding(string $body)
    {
        return preg_match('#[^\n]{990}#', $body)
            ? self::ENCODING_QUOTED_PRINTABLE
            : (preg_match('#[\x80-\xFF]#', $body) ? self::ENCODING_8BIT : self::ENCODING_7BIT);
    }

    public static function getEncodingContent(MimeMessageBaseBean $bean)
    {
        switch ($bean->getContentTransferEncoding()) {
            case self::ENCODING_QUOTED_PRINTABLE:
                $body = quoted_printable_encode($bean->getBody());
                break;

            case self::ENCODING_BASE64:
                $body = rtrim(chunk_split(base64_encode($bean->getBody()), self::LINE_LENGTH, self::EOL));
                break;

            case self::ENCODING_7BIT:
                $body = preg_replace('#[\x80-\xFF]+#', '', $bean->getBody());
            // break omitted

            case self::ENCODING_8BIT:
                $body = str_replace(["\x00", "\r"], '', $bean->getBody());
                $body = str_replace("\n", self::EOL, $body);
                break;
            default:
                throw new InvalidStateException('Unknown encoding.');
        }
        return $body;
    }

    public static function fixEncoding(string $s): string
    {
        // removes xD800-xDFFF, x110000 and higher
        return htmlspecialchars_decode(htmlspecialchars($s, ENT_NOQUOTES | ENT_IGNORE, 'UTF-8'), ENT_NOQUOTES);
    }
}