<?php
namespace Lee2son\Crypto;

class Base64 {
    const REPLACE_PLUS = '+';
    const REPLACE_DIVISOR = '-';
    const REPLACE_EQUAL = '';

    public static function encode($str)
    {
        $str = base64_encode($str);
        $str = str_replace('+', static::REPLACE_PLUS, $str);
        $str = str_replace('/', static::REPLACE_DIVISOR, $str);
        $str = str_replace('=', static::REPLACE_EQUAL, $str);
        return $str;
    }

    public static function decode($str)
    {
        $str = str_replace(static::REPLACE_PLUS, '+', $str);
        $str = str_replace(static::REPLACE_DIVISOR, '/', $str);
        if(static::REPLACE_EQUAL) {
            $str = str_replace(static::REPLACE_EQUAL, '=', $str);
        }
        $str = base64_decode($str);
        return $str;
    }
}