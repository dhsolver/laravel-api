<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    /**
     * Defines the valid options for pricing types
     *
     * @var array
     */
    public static $PRICING_TYPES = ['free', 'premium'];

    /**
     * Defines the valid options for tour types
     *
     * @var array
     */
    public static $TOUR_TYPES = ['tour', 'adventure'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Defines the relatioship of all the tours stops
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stops()
    {
        return $this->hasMany(TourStop::class);
    }

    /**
     * Returns the next free number in the order sequence
     * for the Tour's stops.
     *
     * @return void
     */
    public function getNextStopOrder()
    {
        return $this->stops()
            ->select(\DB::raw('coalesce(max(`order`), 0) as max_order'))
            ->get()
            ->first()
            ->max_order + 1;
    }

    /**
     * Increases the order at the given index for all stops
     * that belong to this tour.
     *
     * @param [type] $order
     * @return void
     */
    public function increaseOrderAt($order)
    {
        TourStop::where('tour_id', $this->id)
            ->where('order', '>=', $order)
            ->increment('order');
    }
}
