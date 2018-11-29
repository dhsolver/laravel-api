<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Activity extends Model
{
    /**
     * The table name for the model.
     *
     * @var string
     */
    protected $table = 'activity';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get all of the owning actionable models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function actionable()
    {
        return $this->morphTo();
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Get all activity since timestamp.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @param \Carbon\Carbon $timestamp
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSince($query, $timestamp)
    {
        if (! empty($timestamp)) {
            return $query->where('created_at', '>=', $timestamp);
        }

        return $query;
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
            $startDate = Carbon::parse($start . ' 00:00:00')->setTimezone('UTC')->toDateTimeString();
            $endDate = Carbon::parse($end . ' 23:59:59')->setTimezone('UTC')->toDateTimeString();
            return $query->whereBetween('activity.created_at', [$startDate, $endDate]);
        } catch (\Exception $ex) {
            return $query;
        }
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************
}
