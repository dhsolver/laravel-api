<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
    protected $dates = ['started_at', 'finished_at', 'won_trophy_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The custom attributes that are automatically appended to the model.
     *
     * @var array
     */
    protected $appends = ['won_trophy'];

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

    /**
     * Check if user has won a trophy or not
     *
     * @return bool
     */
    public function getWonTrophyAttribute()
    {
        return ! empty($this->won_trophy_at);
    }

    /**
     * Get the expiration date for the prize (if any)
     *
     * @return \Carbon\Carbon
     */
    public function getPrizeExpiresAtAttribute()
    {
        if (empty($this->won_trophy_at)) {
            return null;
        }

        return $this->won_trophy_at->addHours($this->tour->prize_time_limit);
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

    /**
     * Get only the scores that are for Adventure Tours.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForAdventures($query)
    {
        return $query->where('is_adventure', true);
    }

    /**
     * Get only the scores that are for non-adventure Tours.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForRegularTours($query)
    {
        return $query->where('is_adventure', false);
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
    public static function for($tour, $user)
    {
        return self::forTour(modelId($tour))
            ->forUser(modelId($user))
            ->orderByDesc('started_at')
            ->first();
    }

    /**
     * Get only the best scores for the given user and or tour.
     *
     * @param \App\User|int|array $user
     * #param \App\Tour|int|array $tour
     * @return Collection<ScoreCard>
     */
    public static function getBest($user, $tour = null)
    {
        return self::orderBy('points', 'desc')
            ->where(function ($query) {
                return $query->where(function ($q) {
                    return $q->forAdventures()
                          ->finished();
                })
                ->orWhere(function ($q) {
                    return $q->forRegularTours();
                });
            })
            ->where(function ($query) use ($tour) {
                if (empty($tour)) {
                    return $query;
                }
                return $query->where('tour_id', modelid($tour));
            })
            ->where('user_id', modelId($user))
            ->get()
            ->unique('tour_id');
    }
}
