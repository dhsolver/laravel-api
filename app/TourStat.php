<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TourStat extends Model
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

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************
}
