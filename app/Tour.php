<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    /**
     * Defines the valid options for pricing types
     *
     * @var array
     */
    public static $PRICING_TYPES = ['free', 'premium'];

    /**
     * Defines the valid options for tour types
     *
     * @var array
     */
    public static $TOUR_TYPES = ['tour', 'adventure'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Defines the relatioship of all the tours stops
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stops()
    {
        return $this->hasMany(TourStop::class);
    }
}
