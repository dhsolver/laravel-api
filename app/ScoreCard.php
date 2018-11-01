<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ScoreCard extends Model
{
    /**
     * The table name for the model.
     *
     * @var string
     */
    protected $table = 'user_score_cards';

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

    /**
     * Get the owning User relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function user()
    {
        return $this->belongsTo(\App\User::class);
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

    /**
     * Get the scores that pertain to the given User.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @param array|int|\App\User
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForUser($query, $user)
    {
        return $query->where('user_id', modelId($user));
    }

    /**
     * Get only the scores that are finished.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeFinished($query)
    {
        return $query->whereNotNull('finished_at');
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Get the current scorecard for the given tour & user.
     *
     * @param \App\Tour|array|int $tour
     * @param \App\User|array|int $user
     * @return ScoreCard|null
     */
    public static function current($tour, $user)
    {
        return self::forTour(modelId($tour))
            ->forUser(modelId($user))
            ->orderByDesc('started_at')
            ->first();
    }
}
