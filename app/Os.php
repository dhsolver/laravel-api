<?php

namespace App;

class Os
{
    const ANDROID = 'android';
    const IOS = 'ios';
    const WINDOWS = 'windows';
    const MAC = 'mac';
    const LINUX = 'linux';
    const OTHER = 'other';
    
    public static function all()
    {
        return [
            self::ANDROID,
            self::IOS,
            self::WINDOWS,
            self::MAC,
            self::LINUX,
            self::OTHER,
        ];
    }
}
