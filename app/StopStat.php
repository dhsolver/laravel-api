<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class StopStat extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The attributes that should be specifically cast.
     *
     * @var array
     */
    protected $casts = ['final' => 'boolean'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        //
        parent::boot();
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the tour relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function stop()
    {
        return $this->belongsTo(TourStop::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Get the stat for a specific date.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @param string $yyyymmdd
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForDate($query, $yyyymmdd)
    {
        return $query->where('yyyymmdd', $yyyymmdd);
    }

    /**
     * Add query to get activity between the given dates.
     * Defaults to all results if one of the dates is empty or invalid.
     *
     * @param QueryBuilder $query
     * @param string $start
     * @param string $end
     * @return QueryBuilder
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        if (empty($start) || empty($end)) {
            return $query;
        }

        try {
            // TODO: handle client timezones?
            $startDate = Carbon::parse($start . ' 00:00:00')->setTimezone('UTC')->format('Ymd');
            $endDate = Carbon::parse($end . ' 23:59:59')->setTimezone('UTC')->format('Ymd');
            return $query->whereBetween('yyyymmdd', [$startDate, $endDate]);
        } catch (\Exception $ex) {
            return $query;
        }
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************
}
