<?php

namespace EasySwoole\Smtp;

use EasySwoole\Smtp\Exception\Exception;

/**
 * reference link https://github.com/PHPMailer/PHPMailer/blob/master/src/PHPMailer.php
 */
class ContentType
{
    const CHARSET_ASCII = 'us-ascii';
    const CHARSET_ISO88591 = 'iso-8859-1';
    const CHARSET_UTF8 = 'utf-8';

    const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
    const CONTENT_TYPE_TEXT_HTML = 'text/html';
    const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
    const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';

    const ENCODING_7BIT = '7bit';
    const ENCODING_8BIT = '8bit';
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    const CRLF = "\r\n";

    const MAIL_MAX_LINE_LENGTH = 63;

    const MAX_LINE_LENGTH = 998;

    const STD_LINE_LENGTH = 76;

    /** @var string $charset */
    protected $charset = self::CHARSET_ISO88591;

    public function __construct(string $charset)
    {
        $this->charset = $charset;
    }


    public function wrapText($message, $length, $qp_mode = false)
    {
        if ($qp_mode) {
            $soft_break = sprintf(' =%s', self::CRLF);
        } else {
            $soft_break = self::CRLF;
        }
        //If utf-8 encoding is used, we will need to make sure we don't
        //split multibyte characters when we wrap
        $is_utf8 = static::CHARSET_UTF8 === strtolower($this->charset);
        $leLen = strlen(self::CRLF);
        $crlfLen = strlen(self::CRLF);

        $message = static::normalizeBreaks($message);
        //Remove a trailing line break
        if (substr($message, -$leLen) === self::CRLF) {
            $message = substr($message, 0, -$leLen);
        }

        //Split message into lines
        $lines = explode(self::CRLF, $message);
        //Message will be rebuilt in here
        $message = '';
        foreach ($lines as $line) {
            $words = explode(' ', $line);
            $buf = '';
            $firstWord = true;
            foreach ($words as $word) {
                if ($qp_mode && (strlen($word) > $length)) {
                    $spaceLeft = $length - strlen($buf) - $crlfLen;
                    if (!$firstWord) {
                        if ($spaceLeft > 20) {
                            $len = $spaceLeft;
                            if ($is_utf8) {
                                $len = $this->utf8CharBoundary($word, $len);
                            } elseif ('=' === substr($word, $len - 1, 1)) {
                                --$len;
                            } elseif ('=' === substr($word, $len - 2, 1)) {
                                $len -= 2;
                            }
                            $part = substr($word, 0, $len);
                            $word = substr($word, $len);
                            $buf .= ' ' . $part;
                            $message .= $buf . sprintf('=%s', self::CRLF);
                        } else {
                            $message .= $buf . $soft_break;
                        }
                        $buf = '';
                    }
                    while ($word !== '') {
                        if ($length <= 0) {
                            break;
                        }
                        $len = $length;
                        if ($is_utf8) {
                            $len = $this->utf8CharBoundary($word, $len);
                        } elseif ('=' === substr($word, $len - 1, 1)) {
                            --$len;
                        } elseif ('=' === substr($word, $len - 2, 1)) {
                            $len -= 2;
                        }
                        $part = substr($word, 0, $len);
                        $word = (string)substr($word, $len);

                        if ($word !== '') {
                            $message .= $part . sprintf('=%s', self::CRLF);
                        } else {
                            $buf = $part;
                        }
                    }
                } else {
                    $buf_o = $buf;
                    if (!$firstWord) {
                        $buf .= ' ';
                    }
                    $buf .= $word;

                    if ('' !== $buf_o && strlen($buf) > $length) {
                        $message .= $buf_o . $soft_break;
                        $buf = $word;
                    }
                }
                $firstWord = false;
            }
            $message .= $buf . self::CRLF;
        }

        return $message;
    }


    public function utf8CharBoundary($encodedText, $maxLength)
    {
        $foundSplitPos = false;
        $lookBack = 3;
        while (!$foundSplitPos) {
            $lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
            $encodedCharPos = strpos($lastChunk, '=');
            if (false !== $encodedCharPos) {
                //Found start of encoded character byte within $lookBack block.
                //Check the encoded byte value (the 2 chars after the '=')
                $hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
                $dec = hexdec($hex);
                if ($dec < 128) {
                    //Single byte character.
                    //If the encoded char was found at pos 0, it will fit
                    //otherwise reduce maxLength to start of the encoded char
                    if ($encodedCharPos > 0) {
                        $maxLength -= $lookBack - $encodedCharPos;
                    }
                    $foundSplitPos = true;
                } elseif ($dec >= 192) {
                    //First byte of a multi byte character
                    //Reduce maxLength to split at start of character
                    $maxLength -= $lookBack - $encodedCharPos;
                    $foundSplitPos = true;
                } elseif ($dec < 192) {
                    //Middle byte of a multi byte character, look further back
                    $lookBack += 3;
                }
            } else {
                //No encoded character found
                $foundSplitPos = true;
            }
        }

        return $maxLength;
    }

    public function getContentTransferEncoding(string $body)
    {
        return preg_match('#[^\n]{990}#', $body)
            ? self::ENCODING_QUOTED_PRINTABLE
            : (preg_match('#[\x80-\xFF]#', $body) ? self::ENCODING_8BIT : self::ENCODING_7BIT);
    }

    public function encodeString($str, $encoding = self::ENCODING_BASE64)
    {
        $encoded = '';
        switch (strtolower($encoding)) {
            case static::ENCODING_BASE64:
                $encoded = chunk_split(
                    base64_encode($str),
                    static::STD_LINE_LENGTH,
                    static::CRLF
                );
                break;
            case static::ENCODING_7BIT:
            case static::ENCODING_8BIT:
                $encoded = static::normalizeBreaks($str);
                //Make sure it ends with a line break
                if (substr($encoded, -(strlen(static::CRLF))) !== static::CRLF) {
                    $encoded .= static::CRLF;
                }
                break;
            case static::ENCODING_BINARY:
                $encoded = $str;
                break;
            case static::ENCODING_QUOTED_PRINTABLE:
                $encoded = $this->encodeQP($str);
                break;
            default:
                throw new Exception("Unknown encoding.");
        }

        return $encoded;
    }

    public function encodeQP($string)
    {
        return static::normalizeBreaks(quoted_printable_encode($string));
    }


    public function encodeHeader($str, $position = 'text')
    {
        $matchCount = 0;
        switch (strtolower($position)) {
            case 'phrase':
                if (!preg_match('/[\200-\377]/', $str)) {
                    //Can't use addslashes as we don't know the value of magic_quotes_sybase
                    $encoded = addcslashes($str, "\0..\37\177\\\"");
                    if (($str === $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)) {
                        return $encoded;
                    }

                    return "\"$encoded\"";
                }
                $matchCount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
                break;
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'comment':
                $matchCount = preg_match_all('/[()"]/', $str, $matches);
            //fallthrough
            case 'text':
            default:
                $matchCount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
                break;
        }

        if ($this->has8bitChars($str)) {
            $charset = $this->charset;
        } else {
            $charset = static::CHARSET_ASCII;
        }

        //Q/B encoding adds 8 chars and the charset ("` =?<charset>?[QB]?<content>?=`").
        $overhead = 8 + strlen($charset);

        $maxLen = static::MAX_LINE_LENGTH - $overhead;

        //Select the encoding that produces the shortest output and/or prevents corruption.
        if ($matchCount > strlen($str) / 3) {
            //More than 1/3 of the content needs encoding, use B-encode.
            $encoding = 'B';
        } elseif ($matchCount > 0) {
            //Less than 1/3 of the content needs encoding, use Q-encode.
            $encoding = 'Q';
        } elseif (strlen($str) > $maxLen) {
            //No encoding needed, but value exceeds max line length, use Q-encode to prevent corruption.
            $encoding = 'Q';
        } else {
            //No reformatting needed
            $encoding = false;
        }

        switch ($encoding) {
            case 'B':
                if ($this->hasMultiBytes($str)) {
                    //Use a custom function which correctly encodes and wraps long
                    //multibyte strings without breaking lines within a character
                    $encoded = $this->base64EncodeWrapMB($str, "\n");
                } else {
                    $encoded = base64_encode($str);
                    $maxLen -= $maxLen % 4;
                    $encoded = trim(chunk_split($encoded, $maxLen, "\n"));
                }
                $encoded = preg_replace('/^(.*)$/m', ' =?' . $charset . "?$encoding?\\1?=", $encoded);
                break;
            case 'Q':
                $encoded = $this->encodeQ($str, $position);
                $encoded = $this->wrapText($encoded, $maxLen, true);
                $encoded = str_replace('=' . self::CRLF, "\n", trim($encoded));
                $encoded = preg_replace('/^(.*)$/m', ' =?' . $charset . "?$encoding?\\1?=", $encoded);
                break;
            default:
                return $str;
        }

        return trim(static::normalizeBreaks($encoded));
    }

    public function hasMultiBytes($str)
    {
        if (function_exists('mb_strlen')) {
            return strlen($str) > mb_strlen($str, $this->charset);
        }

        //Assume no multibytes (we can't handle without mbstring functions anyway)
        return false;
    }

    public function has8bitChars($text)
    {
        return (bool)preg_match('/[\x80-\xFF]/', $text);
    }

    public function base64EncodeWrapMB($str, $linebreak = null)
    {
        $start = '=?' . $this->charset . '?B?';
        $end = '?=';
        $encoded = '';
        if (null === $linebreak) {
            $linebreak = self::CRLF;
        }

        $mb_length = mb_strlen($str, $this->charset);
        //Each line must have length <= 75, including $start and $end
        $length = 75 - strlen($start) - strlen($end);
        //Average multi-byte ratio
        $ratio = $mb_length / strlen($str);
        //Base64 has a 4:3 ratio
        $avgLength = floor($length * $ratio * .75);

        for ($i = 0; $i < $mb_length; $i += $offset) {
            $lookBack = 0;
            do {
                $offset = $avgLength - $lookBack;
                $chunk = mb_substr($str, $i, $offset, $this->charset);
                $chunk = base64_encode($chunk);
                ++$lookBack;
            } while (strlen($chunk) > $length);
            $encoded .= $chunk . $linebreak;
        }

        //Chomp the last linefeed
        return substr($encoded, 0, -strlen($linebreak));
    }

    public function encodeQ($str, $position = 'text')
    {
        //There should not be any EOL in the string
        $pattern = '';
        $encoded = str_replace(["\r", "\n"], '', $str);
        switch (strtolower($position)) {
            case 'phrase':
                //RFC 2047 section 5.3
                $pattern = '^A-Za-z0-9!*+\/ -';
                break;
            /*
             * RFC 2047 section 5.2.
             * Build $pattern without including delimiters and []
             */
            /* @noinspection PhpMissingBreakStatementInspection */
            case 'comment':
                $pattern = '\(\)"';
            /* Intentional fall through */
            case 'text':
            default:
                //RFC 2047 section 5.1
                //Replace every high ascii, control, =, ? and _ characters
                $pattern = '\000-\011\013\014\016-\037\075\077\137\177-\377' . $pattern;
                break;
        }
        $matches = [];
        if (preg_match_all("/[{$pattern}]/", $encoded, $matches)) {
            //If the string contains an '=', make sure it's the first thing we replace
            //so as to avoid double-encoding
            $eqkey = array_search('=', $matches[0], true);
            if (false !== $eqkey) {
                unset($matches[0][$eqkey]);
                array_unshift($matches[0], '=');
            }
            foreach (array_unique($matches[0]) as $char) {
                $encoded = str_replace($char, '=' . sprintf('%02X', ord($char)), $encoded);
            }
        }
        //Replace spaces with _ (more readable than =20)
        //RFC 2047 section 4.2(2)
        return str_replace(' ', '_', $encoded);
    }


    public static function _mime_types($ext = '')
    {
        $mimes = [
            'xl' => 'application/excel',
            'js' => 'application/javascript',
            'hqx' => 'application/mac-binhex40',
            'cpt' => 'application/mac-compactpro',
            'bin' => 'application/macbinary',
            'doc' => 'application/msword',
            'word' => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'class' => 'application/octet-stream',
            'dll' => 'application/octet-stream',
            'dms' => 'application/octet-stream',
            'exe' => 'application/octet-stream',
            'lha' => 'application/octet-stream',
            'lzh' => 'application/octet-stream',
            'psd' => 'application/octet-stream',
            'sea' => 'application/octet-stream',
            'so' => 'application/octet-stream',
            'oda' => 'application/oda',
            'pdf' => 'application/pdf',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'mif' => 'application/vnd.mif',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'wbxml' => 'application/vnd.wap.wbxml',
            'wmlc' => 'application/vnd.wap.wmlc',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'dxr' => 'application/x-director',
            'dvi' => 'application/x-dvi',
            'gtar' => 'application/x-gtar',
            'php3' => 'application/x-httpd-php',
            'php4' => 'application/x-httpd-php',
            'php' => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'phps' => 'application/x-httpd-php-source',
            'swf' => 'application/x-shockwave-flash',
            'sit' => 'application/x-stuffit',
            'tar' => 'application/x-tar',
            'tgz' => 'application/x-tar',
            'xht' => 'application/xhtml+xml',
            'xhtml' => 'application/xhtml+xml',
            'zip' => 'application/zip',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mp2' => 'audio/mpeg',
            'mp3' => 'audio/mpeg',
            'm4a' => 'audio/mp4',
            'mpga' => 'audio/mpeg',
            'aif' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'ram' => 'audio/x-pn-realaudio',
            'rm' => 'audio/x-pn-realaudio',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'ra' => 'audio/x-realaudio',
            'wav' => 'audio/x-wav',
            'mka' => 'audio/x-matroska',
            'bmp' => 'image/bmp',
            'gif' => 'image/gif',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'heif' => 'image/heif',
            'heifs' => 'image/heif-sequence',
            'heic' => 'image/heic',
            'heics' => 'image/heic-sequence',
            'eml' => 'message/rfc822',
            'css' => 'text/css',
            'html' => 'text/html',
            'htm' => 'text/html',
            'shtml' => 'text/html',
            'log' => 'text/plain',
            'text' => 'text/plain',
            'txt' => 'text/plain',
            'rtx' => 'text/richtext',
            'rtf' => 'text/rtf',
            'vcf' => 'text/vcard',
            'vcard' => 'text/vcard',
            'ics' => 'text/calendar',
            'xml' => 'text/xml',
            'xsl' => 'text/xml',
            'wmv' => 'video/x-ms-wmv',
            'mpeg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mp4' => 'video/mp4',
            'm4v' => 'video/mp4',
            'mov' => 'video/quicktime',
            'qt' => 'video/quicktime',
            'rv' => 'video/vnd.rn-realvideo',
            'avi' => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
        ];
        $ext = strtolower($ext);
        if (array_key_exists($ext, $mimes)) {
            return $mimes[$ext];
        }

        return 'application/octet-stream';
    }

    public static function filenameToType($filename)
    {
        //In case the path is a URL, strip any query string before getting extension
        $qpos = strpos($filename, '?');
        if (false !== $qpos) {
            $filename = substr($filename, 0, $qpos);
        }
        $ext = static::mb_pathinfo($filename, PATHINFO_EXTENSION);

        return static::_mime_types($ext);
    }

    public static function mb_pathinfo($path, $options = null)
    {
        $ret = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
        $pathInfo = [];
        if (preg_match('#^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^.\\\\/]+?)|))[\\\\/.]*$#m', $path, $pathInfo)) {
            if (array_key_exists(1, $pathInfo)) {
                $ret['dirname'] = $pathInfo[1];
            }
            if (array_key_exists(2, $pathInfo)) {
                $ret['basename'] = $pathInfo[2];
            }
            if (array_key_exists(5, $pathInfo)) {
                $ret['extension'] = $pathInfo[5];
            }
            if (array_key_exists(3, $pathInfo)) {
                $ret['filename'] = $pathInfo[3];
            }
        }
        switch ($options) {
            case PATHINFO_DIRNAME:
            case 'dirname':
                return $ret['dirname'];
            case PATHINFO_BASENAME:
            case 'basename':
                return $ret['basename'];
            case PATHINFO_EXTENSION:
            case 'extension':
                return $ret['extension'];
            case PATHINFO_FILENAME:
            case 'filename':
                return $ret['filename'];
            default:
                return $ret;
        }
    }

    public static function normalizeBreaks($text, $breakType = null)
    {
        if (null === $breakType) {
            $breakType = self::CRLF;
        }
        //Normalise to \n
        $text = str_replace([self::CRLF, "\r"], "\n", $text);
        //Now convert LE as needed
        if ("\n" !== $breakType) {
            $text = str_replace("\n", $breakType, $text);
        }

        return $text;
    }

}
