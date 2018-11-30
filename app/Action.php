<?php

namespace App;

class Action
{
    /*
    |--------------------------------------------------------------------------
    | Action Constants
    |--------------------------------------------------------------------------
    |
    | These constants are helpers for the the 'action' field in
    | the analytics system.
    |
    */

    const DOWNLOAD = 'download';
    const START = 'start';
    const STOP = 'stop';
    const SHARE = 'share';
    const LIKE = 'like';
    const VISIT = 'visit';

    /**
     * Get array of all the Action types.
     *
     * @return array
     */
    public static function all()
    {
        return [
            self::DOWNLOAD, self::START, self::STOP, self::SHARE, self::LIKE, self::VISIT,
        ];
    }
}
