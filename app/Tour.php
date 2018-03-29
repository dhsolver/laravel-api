<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    public static $PRICING_TYPES = ['free', 'premium'];
    public static $TOUR_TYPES = ['tour', 'adventure'];

    protected $guarded = ['id'];
}
