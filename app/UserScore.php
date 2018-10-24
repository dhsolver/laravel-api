<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Adventure\AdventureCalculator;

class UserScore extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to dates.
     *
     * @var array
     */
    protected $dates = ['started_at', 'finished_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'won_trophy' => 'bool',
    ];

    /**
     * Handles the model boot options.
     *
     * @return void
     */
    protected static function boot()
    {
        static::saving(function ($model) {
            // auto-calculate a new score when the Tour is finished.
            if ($model->isDirty('finished_at')) {
                $ac = new AdventureCalculator($model->tour);
                $model->points = $ac->calculatePoints($model->duration);
                $model->won_trophy = $ac->scoreQualifiesForTrophy($model->points);
            }
        });

        parent::boot();
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the Tour relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function tour()
    {
        return $this->belongsTo(\App\Tour::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get the total time it took for the user to do the Tour.
     *
     * @return int
     */
    public function getDurationAttribute()
    {
        $end = $this->finished_at ?: Carbon::now();

        return intval(ceil($this->started_at->diffInMinutes($end)));
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Get the scores that pertain to the given Tour.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @param array|int|\App\Tour
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForTour($query, $tour)
    {
        return $query->where('tour_id', modelId($tour));
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************
}
