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
     * The custom attributes that are automatically appended to the model.
     *
     * @var array
     */
    protected $appends = ['main_image_path', 'image_1_path', 'image_2_path', 'image_3_path'];

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

    /**
     * Returns the full qualified http path for the tour's main image.
     *
     * @return void
     */
    public function getMainImagePathAttribute()
    {
        if (empty($this->main_image)) {
            return null;
        }

        return config('filesystems.disks.s3.url') . $this->main_image;
    }

    /**
     * Returns the full qualified http path for the tour's first image.
     *
     * @return void
     */
    public function getImage1PathAttribute()
    {
        if (empty($this->image_1)) {
            return null;
        }

        return config('filesystems.disks.s3.url') . $this->image_1;
    }

    /**
     * Returns the full qualified http path for the tour's second image.
     *
     * @return void
     */
    public function getImage2PathAttribute()
    {
        if (empty($this->image_2)) {
            return null;
        }

        return config('filesystems.disks.s3.url') . $this->image_2;
    }

    /**
     * Returns the full qualified http path for the tour's third image.
     *
     * @return void
     */
    public function getImage3PathAttribute()
    {
        if (empty($this->image_3)) {
            return null;
        }

        return config('filesystems.disks.s3.url') . $this->image_3;
    }
}
