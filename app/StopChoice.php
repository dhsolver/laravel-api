<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StopChoice extends Model
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
        'tour_stop_id' => 'int',
        'next_stop_id' => 'int',
        'order' => 'int'
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // auto-update choice order on creation
        self::creating(function ($choice) {
            if (empty($choice->order)) {
                $choice->order = self::getNextOrder($choice->tour_stop_id);
            }
        });
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * A stop choice belongs to a tour stop.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stop()
    {
        return $this->belongsTo(TourStop::class);
    }

    /**
     * A stop has one next stop.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function nextStop()
    {
        return $this->hasOne(TourStop::class, 'id', 'next_stop_id');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Returns the next free number in the order sequence
     * for the given TourStop's Choices.
     *
     * @param int $stopId
     * @return int
     */
    public static function getNextOrder($stopId)
    {
        return self::where('tour_stop_id', $stopId)
            ->select(\DB::raw('coalesce(max(`order`), 0) as max_order'))
            ->get()
            ->first()
            ->max_order + 1;
    }
}
