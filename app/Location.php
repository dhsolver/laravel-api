<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float'
    ];

    /**
     * Determine if the location has coordinates.
     *
     * @return boolean
     */
    public function hasCoordinates()
    {
        return (! empty($this->longitude) && ! empty($this->latitude));
    }
}
