<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chris
 * Date: 6/19/13
 * Time: 1:01 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Messages;

define('BIG_ENDIAN', pack('L', 1) === pack('N', 1));

class Pack {
    static function unpack_int32be($bin){
        list(,$int) = unpack("N", $bin);
        return $int;
    }

    static function pack_int32be($int){
        $packed = pack("N", $int);
        return $packed;
    }

    public static function ntoh($bin)
    {
        if(!BIG_ENDIAN)
            $bin = strrev($bin);

        return $bin;
    }

    public static function pack_int64be($value){
//        $value = PHP_INT_MAX;
        $highMap = 0xffffffff00000000;
        $lowMap = 0x00000000ffffffff;
        $higher = ($value & $highMap) >>32;
        $lower = $value & $lowMap;
        return pack('NN', $higher, $lower);
    }

    public static function unpack_int64be($packed){
        list($higher, $lower) = array_values(unpack('N2', $packed));
        return $higher << 32 | $lower;
    }
}