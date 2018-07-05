<?php

namespace App\Console\Commands\ItourMobile\Models;

use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class TourStop extends Model
{
    use ReadOnlyTrait;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'itourmobile';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tour_stops';

    /**
     * Converts the address information on the model into attributes
     * that can be used to create a Location object.
     *
     * @return array
     */
    public function getLocationAttribute()
    {
        return [
            'address1' => empty($this->stop_address) ? null : $this->stop_address,
            // 'city' => empty($this->stop_city) ? null : $this->stop_city,
            // 'state' => empty($this->stop_state) ? null : $this->stop_state,
            // 'zipcode' => empty($this->stop_weather_zip) ? null : $this->stop_weather_zip,
            'latitude' => empty($this->stop_lat) ? null : $this->stop_lat,
            'longitude' => empty($this->stop_lon) ? null : $this->stop_lon,
        ];
    }

    public function getVideoUrlAttribute()
    {
        if (empty($this->stop_vid_external)) {
            return null;
        }

        if (str_contains($this->stop_vid_external, '://')) {
            return $this->stop_vid_external;
        }

        return 'https://youtu.be/' . $this->stop_vid_external;
    }

    public function getOrderAttribute()
    {
        return intval($this->stop_order);
    }

    public function getTwitterUrlAttribute()
    {
        if (empty($this->twitter)) {
            return null;
        }

        return 'https://twitter.com/' . $this->twitter;
    }

    public function images()
    {
        return $this->hasMany(StopImage::class, 'stop_id', 'stop_id')
            ->orderBy('image_num');
    }
}
