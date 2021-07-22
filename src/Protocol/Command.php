<?php


namespace EasySwoole\Smtp\Protocol;


class Command
{
    const SP = " ";

    const CRLF = "\r\n";

    public static function ehlo($domain)
    {
        return self::payload("ehlo{SP}{$domain}{CRLF}");
    }

    public static function auth($para)
    {
        return self::payload("auth{SP}{$para}{CRLF}");
    }

    public static function mail($reversePath)
    {
        return self::payload("mail{SP}from:<{$reversePath}>{CRLF}");
    }

    public static function rcpt($forWordPath)
    {
        return self::payload("rcpt{SP}to:<{$forWordPath}>{CRLF}");
    }

    public static function dataStart()
    {
        return self::payload("data{CRLF}");
    }

    public static function dataEnd()
    {
        return self::payload(".{CRLF}");
    }

    public static function quit()
    {
        return self::payload("quit{CRLF}");
    }

    public static function raw($raw)
    {
        return self::payload("{$raw}{CRLF}");
    }

    private static function payload(string $str)
    {
        return str_replace([
            "{SP}",
            "{CRLF}"
        ], [self::SP, self::CRLF], $str);
    }
}