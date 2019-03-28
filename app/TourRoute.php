<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TourRoute extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relationships to always load with the model.
     *
     * @var array
     */
    public $with = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float'
    ];

    /**
     * Handles the model boot options.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        // auto-update route order on creation
        self::creating(function ($route) {
            if (empty($route->order)) {
                $route->order = self::getNextOrder($route->tour_id);
            }
        });
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
        return $this->belongsTo(Tour::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER METHODS
    // **********************************************************

    /**
     * Returns the next free number in the order sequence
     * for the given TourStop's Choices.
     *
     * @param int $tourId
     * @return int
     */
    public static function getNextOrder($tourId)
    {
        return self::where('tour_id', $tourId)
            ->select(\DB::raw('coalesce(max(`order`), 0) as max_order'))
            ->get()
            ->first()
            ->max_order + 1;
    }
}
