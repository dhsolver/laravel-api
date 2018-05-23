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
     * Relatioships to always load with the model.
     *
     * @var array
     */
    public $with = ['location', 'choices', 'image1', 'image2', 'image3', 'mainImage', 'audio'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_multiple_choice' => 'bool',
        'latitude' => 'float',
        'longitude' => 'float',
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
                'locationable_type' => 'App\TourStop',
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

    public function audio()
    {
        return $this->hasOne(Media::class, 'id', 'audio_id');
    }

    /**
     * Defines the location relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location()
    {
        return $this->hasOne('App\Location', 'locationable_id', 'id')
            ->where('locationable_type', 'App\TourStop');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

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

    // **********************************************************
    // OTHER METHODS
    // **********************************************************

    /**
     * Creates or updates all choices using the given array.
     *
     * @param [type] $newChoices
     * @return void
     */
    public function updateChoices($newChoices)
    {
        if (!is_array($newChoices)) {
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
}
