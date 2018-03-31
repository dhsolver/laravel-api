<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TourStop extends Model
{
    /**
     * Defines the valid options for location types.
     *
     * @var array
     */
    public static $LOCATION_TYPES = ['map', 'address', 'gps'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Returns the relationship of the tour that the stop belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
