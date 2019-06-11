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
    const START_STOP = 'start_stop';
    const SHARE = 'share';
    const LIKE = 'like';
    const VISIT = 'visit';
    const REDEEMED_PRIZE = 'redeemed_prize';

    /**
     * Get array of all the Action types.
     *
     * @return array
     */
    public static function all()
    {
        return [
            self::DOWNLOAD, self::START, self::STOP, self::START_STOP, self::SHARE, self::LIKE, self::VISIT, self::REDEEMED_PRIZE
        ];
    }
}
