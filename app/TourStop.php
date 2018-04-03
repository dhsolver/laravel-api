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
     * Defines the attributes that are images.
     *
     * @var array
     */
    public static $imageAttributes = ['main_image', 'image_1', 'image_2', 'image_3'];

    /**
     * Defines the attributes that are images.
     *
     * @var array
     */
    public static $audioAttributes = ['audio'];

    /**
     * Returns the relationship of the tour that the stop belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Defines the default ordering for stops using order column.
     *
     * @param [type] $query
     * @return void
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'ASC');
    }
}
