<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Rules\YoutubeVideo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tour extends Model
{
    use SoftDeletes;

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
    public static $TOUR_TYPES = ['indoor', 'outdoor', 'adventure'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The custom attributes that are automatically appended to the model.
     *
     * @var array
     */
    protected $appends = ['stops_count'];

    /**
     * Relationships to always load.
     *
     * @var array
     */
    protected $with = ['location', 'image1', 'image2', 'image3', 'mainImage', 'startImage', 'endImage', 'pinImage', 'trophyImage', 'introAudio', 'backgroundAudio'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'has_prize' => 'bool',
    ];

    /**
     * The attributes that should be converted to Carbon dates.
     *
     * @var array
     */
    protected $dates = ['published_at'];

    /**
     * Handles the model boot options.
     *
     * @return void
     */
    protected static function boot()
    {
        // always attach a location when a Tour is created.
        static::created(function ($model) {
            $model->location()->create([
                'locationable_id' => $model->id,
                'locationable_type' => 'App\Tour',
            ]);
        });

        parent::boot();
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Gets the publish submissions relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publishSubmissions()
    {
        return $this->hasMany(PublishTourSubmission::class, 'tour_id', 'id');
    }

    /**
     * Defines the location relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location()
    {
        return $this->hasOne('App\Location', 'locationable_id', 'id')
            ->where('locationable_type', 'App\Tour');
    }

    /**
     * Defines the relatioship of all the tours stops
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stops()
    {
        return $this->hasMany(TourStop::class)
            ->ordered();
    }

    public function mainImage()
    {
        return $this->hasOne(Media::class, 'id', 'main_image_id');
    }

    public function image1()
    {
        return $this->hasOne(Media::class, 'id', 'image1_id');
    }

    public function image2()
    {
        return $this->hasOne(Media::class, 'id', 'image2_id');
    }

    public function image3()
    {
        return $this->hasOne(Media::class, 'id', 'image3_id');
    }

    public function startImage()
    {
        return $this->hasOne(Media::class, 'id', 'start_image_id');
    }

    public function endImage()
    {
        return $this->hasOne(Media::class, 'id', 'end_image_id');
    }

    public function pinImage()
    {
        return $this->hasOne(Media::class, 'id', 'pin_image_id');
    }

    public function trophyImage()
    {
        return $this->hasOne(Media::class, 'id', 'trophy_image_id');
    }

    public function introAudio()
    {
        return $this->hasOne(Media::class, 'id', 'intro_audio_id');
    }

    public function backgroundAudio()
    {
        return $this->hasOne(Media::class, 'id', 'background_audio_id');
    }

    public function startPoint()
    {
        return $this->hasOne(TourStop::class, 'id', 'start_point_id');
    }

    public function endPoint()
    {
        return $this->hasOne(TourStop::class, 'id', 'end_point_id');
    }

    /**
     * Get all the tour's stop routes.
     *
     * @return void
     */
    public function stopRoutes()
    {
        return $this->hasMany(StopRoute::class, 'tour_id', 'id')
            ->orderBy('order');
    }

    /**
     * Get the tours route.
     *
     * @return void
     */
    public function route()
    {
        return $this->hasMany(TourRoute::class, 'tour_id', 'id')
            ->orderBy('order');
    }

    /**
     * A Tour morphs to many actionables.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activity()
    {
        return $this->morphMany(Activity::class, 'actionable');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get whether the tour is currently waiting for publish approval.
     *
     * @return boolean
     */
    public function getIsAwaitingApprovalAttribute()
    {
        if ($this->isPublished) {
            return false;
        }

        return $this->publishSubmissions()
            ->pending()
            ->exists();
    }

    /**
     * Returns the full facebook url.
     *
     * @return void
     */
    public function getFacebookUrlPathAttribute()
    {
        if (empty($this->facebook_url)) {
            return null;
        }

        return 'https://www.facebook.com/' . $this->facebook_url;
    }

    /**
     * Returns the full twitter url.
     *
     * @return void
     */
    public function getTwitterUrlPathAttribute()
    {
        if (empty($this->twitter_url)) {
            return null;
        }

        return 'https://www.twitter.com/' . $this->twitter_url;
    }

    /**
     * Returns the full instagram url.
     *
     * @return void
     */
    public function getInstagramUrlPathAttribute()
    {
        if (empty($this->instagram_url)) {
            return null;
        }

        return 'https://www.instagram.com/' . $this->instagram_url;
    }

    /**
     * Gets whether the tour has been publishes or not.
     *
     * @return void
     */
    public function getIsPublishedAttribute()
    {
        return ! empty($this->published_at);
    }

    /**
     * Mutator for video_url
     *
     * @param [String] $value
     * @return void
     */
    public function setVideoUrlAttribute($value)
    {
        $this->attributes['video_url'] = YoutubeVideo::formatUrl($value);
    }

    /**
     * Mutator for start_video_url
     *
     * @param [String] $value
     * @return void
     */
    public function setStartVideoUrlAttribute($value)
    {
        $this->attributes['start_video_url'] = YoutubeVideo::formatUrl($value);
    }

    /**
     * Mutator for end_video_url
     *
     * @param [String] $value
     * @return void
     */
    public function setEndVideoUrlAttribute($value)
    {
        $this->attributes['end_video_url'] = YoutubeVideo::formatUrl($value);
    }

    /**
     * Mutator for facebook_url.
     *
     * @param [type] $value
     * @return void
     */
    public function setFacebookUrlAttribute($value)
    {
        if (! empty($value) && ! starts_with($value, ['http:', 'https:'])) {
            $this->attributes['facebook_url'] = 'https://' . $value;
        } else {
            $this->attributes['facebook_url'] = $value;
        }
    }

    /**
     * Mutator for instagram_url.
     *
     * @param [type] $value
     * @return void
     */
    public function setInstagramUrlAttribute($value)
    {
        if (! empty($value) && ! starts_with($value, ['http:', 'https:'])) {
            $this->attributes['instagram_url'] = 'https://' . $value;
        } else {
            $this->attributes['instagram_url'] = $value;
        }
    }

    /**
     * Mutator for twitter_url.
     *
     * @param [type] $value
     * @return void
     */
    public function setTwitterUrlAttribute($value)
    {
        if (! empty($value) && ! starts_with($value, ['http:', 'https:'])) {
            $this->attributes['twitter_url'] = 'https://' . $value;
        } else {
            $this->attributes['twitter_url'] = $value;
        }
    }

    /**
     * Get count on stops relationship
     *
     * @return int
     */
    public function getStopsCountAttribute()
    {
        return $this->stops()->count();
    }

    /**
     * Get the list of stop ids in order.
     *
     * @return void
     */
    public function getStopOrderAttribute()
    {
        return $this->stops->map(function ($s) {
            return $s->id;
        });
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Add distance field to the select query and sort by distance.
     *
     * @param Illuminate\Database\Query\Builder $query
     * @param float $lat
     * @param float $lon
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeDistanceFrom($query, $lat, $lon)
    {
        if ($lat == 0 || $lon == 0) {
            return $query;
        }

        $distanceQuery = "round(3959 * acos( cos( radians($lat) ) * cos( radians(locations.latitude) ) * cos( radians(locations.longitude) - radians($lon)) + sin(radians($lat)) * sin( radians(locations.latitude) )), 2)";

        return \App\Tour::leftJoin('locations', function ($join) {
            $join->on('tours.id', '=', 'locations.locationable_id')
                ->where('locations.locationable_type', '=', "App\Tour");
        })
            ->selectRaw("tours.*, ($distanceQuery) as distance")
            ->whereNotNull('locations.latitude')
            ->whereNotNull('locations.longitude')
            ->orderBy('distance');
    }

    public function scopeSearch($query, $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function ($query) use ($keyword) {
            $query->where('title', 'like', "%$keyword%")
                ->orWhere('description', 'like', "%$keyword%");
        });
    }

    // **********************************************************
    // OTHER METHODS
    // **********************************************************

    /**
     * Publishes the tour.
     *
     * @return void
     */
    public function publish()
    {
        $this->update([
            'published_at' => Carbon::now(),
            'last_published_at' => Carbon::now(),
        ]);
    }

    /**
     * Returns the next free number in the order sequence
     * for the Tour's stops.
     *
     * @return void
     */
    public function getNextStopOrder()
    {
        return $this->stops()
            ->select(\DB::raw('coalesce(max(`order`), 0) as max_order'))
            ->get()
            ->first()
            ->max_order + 1;
    }

    /**
     * Increases the order at the given index for all stops
     * that belong to this tour.
     *
     * @param [type] $order
     * @return void
     */
    public function increaseOrderAt($order)
    {
        TourStop::where('tour_id', $this->id)
            ->where('order', '>=', $order)
            ->increment('order');
    }

    /**
     * Sync Tour route from array of coordinates.
     *
     * @param array $coordinates
     * @return void
     */
    public function syncRoute($coordinates)
    {
        $this->route()->delete();

        if (empty($coordinates)) {
            return;
        }

        foreach ($coordinates as $latLng) {
            $this->route()->create([
                'latitude' => $latLng['lat'],
                'longitude' => $latLng['lng'],
            ]);
        }
    }

    /**
     * Determine if the Tour is free.
     *
     * @return boolean
     */
    public function isFree()
    {
        return $this->pricing_type == 'free';
    }

    /**
     * Get the Tour's participants relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'user_joined_tours', 'tour_id', 'user_id');
    }

    /**
     * Scope to only show published Tours.
     *
     * @param [type] $query
     * @return void
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
