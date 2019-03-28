<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TourStop extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Relationships to always load with the model.
     *
     * @var array
     */
    public $with = ['location', 'choices', 'image1', 'image2', 'image3', 'mainImage', 'introAudio', 'backgroundAudio', 'routes'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_multiple_choice' => 'bool',
        'play_radius' => 'float'
    ];

    /**
     * Handles the model boot options.
     *
     * @return void
     */
    protected static function boot()
    {
        // always attach a location when a TourStop is created.
        static::created(function ($model) {
            $model->location()->create([
                'locationable_id' => $model->id,
                'locationable_type' => 'App\TourStop'
            ]);
        });

        parent::boot();
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

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
     * A tour stop can have many choices, always in order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function choices()
    {
        return $this->hasMany(StopChoice::class)
            ->orderBy('order');
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
     * Defines the location relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location()
    {
        return $this->hasOne(Location::class, 'locationable_id', 'id')
            ->where('locationable_type', 'App\TourStop');
    }

    /**
     * Get all the stops's next routes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function routes()
    {
        return $this->hasMany(StopRoute::class, 'stop_id', 'id')
            ->orderBy('next_stop_id')
            ->orderBy('order');
    }

    /**
     * A Stop morphs to many actionables.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activity()
    {
        return $this->morphMany(Activity::class, 'actionable');
    }

    /**
     * Get the stats summary relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function stats()
    {
        return $this->hasMany(StopStat::class, 'stop_id');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Order the stops by their order column.
     *
     * @param Illuminate\Database\Query\Builder $query
     * @return Illuminate\Database\Query\Builder
    */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'ASC');
    }

    // **********************************************************
    // OTHER METHODS
    // **********************************************************

    /**
     * Creates or updates all choices using the given array.
     *
     * @param array] $newChoices
     * @return bool
     */
    public function updateChoices($newChoices)
    {
        if (! is_array($newChoices)) {
            return false;
        }

        $choices = collect($newChoices);
        $ids = $choices->pluck('id');
        $this->choices()->whereNotIn('id', $ids)->delete();

        foreach ($newChoices as $data) {
            $c = empty($data['id']) ? null : StopChoice::find($data['id']);

            if (empty($c)) {
                StopChoice::create($data);
            } else {
                $c->update($data);
            }
        }

        return true;
    }

    /**
     * Sync all Stop routes from array.
     *
     * @param array $routes
     * @return void
     */
    public function syncRoutes($routes)
    {
        $this->routes()->delete();

        if (empty($routes)) {
            return;
        }

        foreach ($routes as $nextStop) {
            foreach ($nextStop['route'] as $item) {
                $this->routes()->create([
                    'tour_id' => $this->tour_id,
                    'stop_id' => $this->id,
                    'next_stop_id' => $nextStop['next_stop_id'],
                    'latitude' => $item['lat'],
                    'longitude' => $item['lng']
                ]);
            }
        }
    }

    /**
     * Check if this stop is the starting point for a Tour.
     *
     * @return bool
     */
    public function isStartingPoint()
    {
        return Tour::where('start_point_id', $this->id)
            ->exists();
    }

    /**
     * Check if this stop is the end point for a Tour.
     *
     * @return bool
     */
    public function isEndingPoint()
    {
        return Tour::where('end_point_id', $this->id)
            ->exists();
    }
}
