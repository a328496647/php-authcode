<?php
namespace a328496647\Authcode;

class Authcode {
    public static $salt_length = 22;

    public static function encrypt($str, $key, $expiry = 0) {
        return static::authcode($str, $key, true, $expiry);
    }

    public static function decrypt($str, $key) {
        return static::authcode($str, $key, false, 0);
    }

    private static function base64Decode($str) {
        $str = str_replace('_', '+', $str);
        $str = str_replace('-', '/', $str);
        $str = base64_decode($str);
        return $str;
    }

    private static function base64Encode($str) {
        $str = base64_encode($str);
        $str = str_replace('+', '_', $str);
        $str = str_replace('/', '-', $str);
        $str = str_replace('=', '', $str);
        return $str;
    }

    private static function uuid() {
        return static::base64Encode(md5(mt_rand(0, 999999) . uniqid() . mt_rand(0, 999999) . microtime() . mt_rand(0, 999999), true));
    }

    private static function authcode($str, $key, $encrypt = false, $expiry = 0) {
        $sl = static::$salt_length;

        $key = md5($key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));

        if($sl > 0) {
            if($encrypt) {
                $keyc = substr(static::uuid(), 0, $sl);
            } else {
                $keyc = substr($str, 0, $sl);
            }
        } else {
            $keyc = '';
        }

        $cryptkey = $keya . md5($keya . $keyc);
        $kl = strlen($cryptkey);

        if($encrypt) {
            $str = sprintf('%010d%s%s', ($expiry ? $expiry + time() : 0), substr(md5($str . $keyb), 0, 16), $str);
        } else {
            $str = static::base64Decode(substr($str, $sl));
        }

        $len = strlen($str);

        $res = '';
        $box = range(0, 255);

        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $kl]);
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $len; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $res .= chr(ord($str[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if(!$encrypt) {
            $expiry = intval(substr($res, 0, 10));
            if(($expiry === 0 || $expiry - time() > 0) && substr($res, 10, 16) == substr(md5(substr($res, 26) . $keyb), 0, 16)) {
                return substr($res, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', static::base64Encode($res));
        }
    }
}