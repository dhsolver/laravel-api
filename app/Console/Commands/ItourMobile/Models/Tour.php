<?php

namespace App\Console\Commands\ItourMobile\Models;

use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class Tour extends Model
{
    use ReadOnlyTrait;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'published_at', 'last_published_at'];

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
    protected $table = 'tours';

    /**
     * Converts the address information on the model into attributes
     * that can be used to create a Location object.
     *
     * @return array
     */
    public function getLocationAttribute()
    {
        $zip = empty($this->tour_weather_zip) ? null : $this->tour_weather_zip;

        if (! empty($zip)) {
            if (strpos($zip, ', ') > 0) {
                $zips = explode(', ', $zip);
                $zip = $zips[0];
            } elseif (strlen($zip) > 12) {
                // why
                $zip = substr($zip, 0, 12);
            }
        }

        return [
            'address1' => empty($this->tour_street_address) ? null : $this->tour_street_address,
            'city' => empty($this->tour_city) ? null : $this->tour_city,
            'state' => empty($this->tour_state) ? null : $this->tour_state,
            'country' => empty($this->tour_country) ? null : $this->tour_country,
            'zipcode' => empty($zip) ? null : $zip,
            'latitude' => empty($this->tour_lat) ? null : $this->tour_lat,
            'longitude' => empty($this->tour_lon) ? null : $this->tour_lon,
        ];
    }

    public function getVideoUrlAttribute()
    {
        if (empty($this->tour_youtube)) {
            return null;
        }

        return 'https://youtu.be/' . $this->tour_youtube;
    }

    public function getTwitterUrlAttribute()
    {
        if (empty($this->twitter)) {
            return null;
        }

        return 'https://twitter.com/' . $this->twitter;
    }

    public function icon()
    {
        return $this->hasOne(TourIcon::class, 'tour_id', 'tour_id')
            ->where('type', 'base');
    }

    public function activeIcon()
    {
        return $this->hasOne(TourIcon::class, 'tour_id', 'tour_id')
            ->where('type', 'active');
    }
}
