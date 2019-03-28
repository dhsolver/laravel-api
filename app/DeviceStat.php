<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DeviceStat extends Model
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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'final' => 'boolean',
        'actions' => 'integer',
        'downloads' => 'integer',
        'visitors' => 'integer'
    ];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the tour relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
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

    /**
     * Query for the given device type and operating system.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForDevice($query, $os, $deviceType)
    {
        if (empty($os) || empty($deviceType)) {
            return $query;
        }

        return $query->where('os', $os)->where('device_type', $deviceType);
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************
}
