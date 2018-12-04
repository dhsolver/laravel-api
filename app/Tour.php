<?php

namespace App;

use App\Points\AdventureCalculator;
use App\Points\TourCalculator;
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
    protected $appends = ['stops_count', 'status', 'is_published', 'is_awaiting_approval'];

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
        'rating' => 'int',
        'prize_time_limit' => 'int',
    ];

    /**
     * The attributes that should be converted to Carbon dates.
     *
     * @var array
     */
    protected $dates = ['published_at'];

    /**
     * Keep the points calculator on the model so it never
     * has to load twice.
     *
     * @var \App\Points\IPointsCalculator
     */
    protected $_calculator;

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
     * Get the creating user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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
     * Defines the relationship of all the tours stops
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stops()
    {
        return $this->hasMany(TourStop::class)
            ->ordered();
    }

    /**
     * Get the main image relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mainImage()
    {
        return $this->hasOne(Media::class, 'id', 'main_image_id');
    }

    /**
     * Get the first image relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function image1()
    {
        return $this->hasOne(Media::class, 'id', 'image1_id');
    }

    /**
     * Get the second image relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function image2()
    {
        return $this->hasOne(Media::class, 'id', 'image2_id');
    }

    /**
     * Get the third image relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function image3()
    {
        return $this->hasOne(Media::class, 'id', 'image3_id');
    }

    /**
     * Get the start image relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function startImage()
    {
        return $this->hasOne(Media::class, 'id', 'start_image_id');
    }

    /**
     * Get the end image relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function endImage()
    {
        return $this->hasOne(Media::class, 'id', 'end_image_id');
    }

    /**
     * Get the map pin image relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pinImage()
    {
        return $this->hasOne(Media::class, 'id', 'pin_image_id');
    }

    /**
     * Get the trophy image relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function trophyImage()
    {
        return $this->hasOne(Media::class, 'id', 'trophy_image_id');
    }

    /**
     * Get the intro audio relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function introAudio()
    {
        return $this->hasOne(Media::class, 'id', 'intro_audio_id');
    }

    /**
     * Get the background audio relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function backgroundAudio()
    {
        return $this->hasOne(Media::class, 'id', 'background_audio_id');
    }

    /**
     * Get the TourStop starting point relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function startPoint()
    {
        return $this->hasOne(TourStop::class, 'id', 'start_point_id');
    }

    /**
     * Get the TourStop end point relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function endPoint()
    {
        return $this->hasOne(TourStop::class, 'id', 'end_point_id');
    }

    /**
     * Get all the tour's stop routes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stopRoutes()
    {
        return $this->hasMany(StopRoute::class, 'tour_id', 'id')
            ->orderBy('order');
    }

    /**
     * Get the tours route.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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

    /**
     * Get the tour's reviews.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the stats summary relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function stats()
    {
        return $this->hasMany(TourStat::class);
    }

    /**
     * Get the device stats summary relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function deviceStats()
    {
        return $this->hasMany(DeviceStat::class);
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

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get the tour's status.
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        if ($this->isPublished) {
            return 'live';
        } elseif ($this->isAwaitingApproval) {
            return 'pending';
        } else {
            return 'draft';
        }
    }

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
     * @return string
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
     * @return string
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
     * @return string
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
     * @return bool
     */
    public function getIsPublishedAttribute()
    {
        return ! empty($this->published_at);
    }

    /**
     * Mutator for video_url.
     *
     * @param string $value
     * @return void
     */
    public function setVideoUrlAttribute($value)
    {
        $this->attributes['video_url'] = YoutubeVideo::formatUrl($value);
    }

    /**
     * Mutator for start_video_url.
     *
     * @param string $value
     * @return void
     */
    public function setStartVideoUrlAttribute($value)
    {
        $this->attributes['start_video_url'] = YoutubeVideo::formatUrl($value);
    }

    /**
     * Mutator for end_video_url.
     *
     * @param string $value
     * @return void
     */
    public function setEndVideoUrlAttribute($value)
    {
        $this->attributes['end_video_url'] = YoutubeVideo::formatUrl($value);
    }

    /**
     * Mutator for facebook_url.
     *
     * @param string $value
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
     * @param string $value
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
     * @param string $value
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
     * @return array
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

        return Tour::leftJoin('locations', function ($join) {
            $join->on('tours.id', '=', 'locations.locationable_id')
                ->where('locations.locationable_type', '=', "App\Tour");
        })
            ->selectRaw("tours.*, ($distanceQuery) as distance")
            ->whereNotNull('locations.latitude')
            ->whereNotNull('locations.longitude')
            ->orderBy('distance');
    }

    /**
     * Full text search of title and description fields.
     *
     * @param Illuminate\Database\Query\Builder $query
     * @param string $keyword
     * @return Illuminate\Database\Query\Builder
     */
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

    /**
     * Add query to only show the given user's favorites.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @param mixed $user
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeFavoritedBy($query, $user)
    {
        if (empty($user)) {
            return $query;
        }

        $user = User::findOrFail(modelId($user));
        return $query->whereIn('tours.id', $user->favorites->pluck('id'));
    }

    // **********************************************************
    // OTHER METHODS
    // **********************************************************

    /**
     * Get the proper points calculator for the Tour.
     *
     * @return \App\Points\IPointsCalculator
     */
    public function calculator()
    {
        if (! empty($this->_calculator)) {
            return $this->_calculator;
        }

        if ($this->isAdventure()) {
            $this->_calculator = new AdventureCalculator($this);
        } else {
            $this->_calculator = new TourCalculator($this);
        }

        return $this->_calculator;
    }

    /**
     * Run Tour Auditor on current Tour.
     *
     * @return array|bool
     */
    public function audit()
    {
        $auditor = new TourAuditor($this);

        if (! $auditor->run()) {
            return $auditor->errors;
        }

        return false;
    }

    /**
     * Publishes the tour.
     *
     * @return bool
     */
    public function publish()
    {
        return $this->update([
            'published_at' => Carbon::now(),
            'last_published_at' => Carbon::now(),
        ]);
    }

    /**
     * Creates a publish request for the tour.
     *
     * @return bool
     */
    public function submitForPublishing()
    {
        if ($this->isAwaitingApproval || $this->publishSubmissions()->create([
            'tour_id' => $this->id,
            'user_id' => $this->user_id,
        ])) {
            return true;
        }

        return false;
    }

    /**
     * Returns the next free number in the order sequence
     * for the Tour's stops.
     *
     * @return int
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
     * @param int $order
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
     * Scope to only show published Tours.
     *
     * @param Illuminate\Database\Query\Builder $query
     * @param boolean $debug
     * @return Illuminate\Database\Query\Builder
     */
    public function scopePublished($query, $debug = false)
    {
        if ($debug) {
            return $query->where(function ($q) {
                return $q->whereNotNull('published_at')
                    ->orWhere('user_id', auth()->user()->id);
            });
        }

        return $query->whereNotNull('published_at');
    }

    /**
     * Helper method to check if tour type is 'adventure'.
     *
     * @return bool
     */
    public function isAdventure()
    {
        return $this->type == TourType::ADVENTURE;
    }

    /**
     * Get the total length of the tour and set the length attribute.
     * Returns bool whether it was able to be updated or not.
     *
     * @return bool
     */
    public function updateLength()
    {
        if ($this->length = $this->calculator()->getTourLength()) {
            $this->save();
            return true;
        }

        return false;
    }
}
