<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TourStop extends Model
{
    /**
     * Defines the valid options for location types.
     *
     * @var array
     */
    public static $LOCATION_TYPES = ['map', 'address', 'gps'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Defines the attributes that are images.
     *
     * @var array
     */
    public static $imageAttributes = ['main_image', 'image_1', 'image_2', 'image_3'];

    /**
     * Defines the attributes that are images.
     *
     * @var array
     */
    public static $audioAttributes = ['audio'];

    /**
     * Relatioships to always load with the model.
     *
     * @var array
     */
    public $with = ['choices'];

    /**
     * Returns the relationship of the tour that the stop belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
    
    /**
     * Defines the default ordering for stops using order column.
     *
     * @param [type] $query
     * @return void
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'ASC');
    }

    /**
     * A tour stop can have many choices, always in order.
     *
     * @return void
     */
    public function choices()
    {
        return $this->hasMany(StopChoice::class)
            ->orderBy('order');
    }

    /**
     * Creates or updates all choices using the given array.
     *
     * @param [type] $newChoices
     * @return void
     */
    public function updateChoices($newChoices)
    {
        if (empty($newChoices)) {
            return false;
        }

        $choices = collect($newChoices);
        $ids = $choices->pluck('id');

        $this->choices()->whereNotIn('id', $ids)->delete();

        foreach ($newChoices as $data) {
            $c = empty($data['id']) ? null : StopChoice::find($data['id']);

            if (empty($c)) {
                StopChoice::create($data);
            } else {
                $c->update($data);
            }
        }

        return true;
    }
}
