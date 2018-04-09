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

    public static function boot()
    {
        self::creating(function ($choice) {
            if (empty($choice->order)) {
                $choice->order = self::getNextOrder($choice->tour_stop_id);
            }
        });
    }

    /**
     * A stop choice belongs to a tour stop.
     *
     * @return void
     */
    public function stop()
    {
        return $this->belongsTo(TourStop::class);
    }

    /**
     * A stop has one next stop.
     *
     * @return void
     */
    public function nextStop()
    {
        return $this->hasOne(TourStop::class, 'id', 'next_stop_id');
    }

    /**
     * Returns the next free number in the order sequence
     * for the given TourStop's Choices.
     *
     * @return void
     */
    public static function getNextOrder($stopId)
    {
        return self::where('tour_stop_id', $stopId)
            ->select(\DB::raw('coalesce(max(`order`), 0) as max_order'))
            ->get()
            ->first()
            ->max_order + 1;
    }

    /**
     * Sorts the results by the StopChoice order.
     *
     * @param [type] $query
     * @return void
     */
    public function scopeOrdered($query)
    {
        $query->orderBy('order');
    }
}
