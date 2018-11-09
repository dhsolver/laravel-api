<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ScoreCardStopsPivot extends Pivot
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'visited_at' => 'date'
    ];
}
