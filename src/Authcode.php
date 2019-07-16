<?php
namespace Lee2son\Crypto;

class Authcode {
    public static function encrypt($str, $key, $expiry = 0) {
        return static::authcode($str, $key, true, $expiry);
    }

    public static function decrypt($str, $key) {
        return static::authcode($str, $key, false, 0);
    }

    private static function authcode($str, $key, $encrypt = false, $expiry = 0) {
        $base64 = new class extends Base64 {
            const REPLACE_DIVISOR = '-';
            const REPLACE_PLUS = '_';
            const REPLACE_EQUAL = '';
        };

        $base64::encode('sadfasdf');

        $sl = 4;

        $key = md5($key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));

        if($sl > 0) {
            if($encrypt) {
                $keyc = substr(md5(uniqid()), 0, $sl);
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
            $str = $base64::decode(substr($str, $sl));
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
            return $keyc . str_replace('=', '', $base64::encode($res));
        }
    }
}