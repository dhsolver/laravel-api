<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Rules\YoutubeVideo;

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
    public static $TOUR_TYPES = ['indoor', 'outdoor', 'adventure'];

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
    protected $appends = ['stops_count', 'main_image_path', 'image_1_path', 'image_2_path', 'image_3_path', 'trophy_image_path', 'intro_audio_path', 'background_audio_path', 'start_image_path', 'end_image_path'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'has_prize' => 'bool',
    ];

    /**
     * Defines the attributes that are images.
     *
     * @var array
     */
    public static $imageAttributes = ['main_image', 'image_1', 'image_2', 'image_3', 'trophy_image', 'start_image', 'end_image'];

    /**
     * Defines the attributes that are images.
     *
     * @var array
     */
    public static $audioAttributes = ['intro_audio', 'background_audio'];

    /**
     * Defines the relatioship of all the tours stops
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stops()
    {
        return $this->hasMany(TourStop::class)
            ->ordered();
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

    /**
     * Returns the full qualified http path for the tour's trophy image.
     *
     * @return void
     */
    public function getTrophyImagePathAttribute()
    {
        if (empty($this->trophy_image)) {
            return null;
        }

        return config('filesystems.disks.s3.url') . $this->trophy_image;
    }

    /**
     * Returns the full qualified http path for the tour's start image.
     *
     * @return void
     */
    public function getStartImagePathAttribute()
    {
        if (empty($this->start_image)) {
            return null;
        }

        return config('filesystems.disks.s3.url') . $this->start_image;
    }

    /**
     * Returns the full qualified http path for the tour's third image.
     *
     * @return void
     */
    public function getEndImagePathAttribute()
    {
        if (empty($this->end_image)) {
            return null;
        }

        return config('filesystems.disks.s3.url') . $this->end_image;
    }

    /**
     * Returns the full facebook url.
     *
     * @return void
     */
    public function getFacebookUrlPathAttribute()
    {
        if (empty($this->facebook_url)) {
            return null;
        }

        return 'https://www.facebook.com/' . $this->facebook_url;
    }

    /**
     * Returns the full twitter url.
     *
     * @return void
     */
    public function getTwitterUrlPathAttribute()
    {
        if (empty($this->twitter_url)) {
            return null;
        }

        return 'https://www.twitter.com/' . $this->twitter_url;
    }

    /**
     * Returns the full instagram url.
     *
     * @return void
     */
    public function getInstagramUrlPathAttribute()
    {
        if (empty($this->instagram_url)) {
            return null;
        }

        return 'https://www.instagram.com/' . $this->instagram_url;
    }

    /**
     * Publishes the tour.
     *
     * @return void
     */
    public function publish()
    {
        $this->update(['published_at' => Carbon::now()]);
    }

    /**
     * Gets whether the tour has been publishes or not.
     *
     * @return void
     */
    public function getIsPublishedAttribute()
    {
        return !empty($this->published_at);
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
     * Get count on stops relationship
     *
     * @return int
     */
    public function getStopsCountAttribute()
    {
        return $this->stops()->count();
    }

    /**
     * Mutator for video_url
     *
     * @param [String] $value
     * @return void
     */
    public function setVideoUrlAttribute($value)
    {
        $this->attributes['video_url'] = YoutubeVideo::formatUrl($value);
    }

    /**
     * Mutator for start_video_url
     *
     * @param [String] $value
     * @return void
     */
    public function setStartVideoUrlAttribute($value)
    {
        $this->attributes['start_video_url'] = YoutubeVideo::formatUrl($value);
    }

    /**
     * Mutator for end_video_url
     *
     * @param [String] $value
     * @return void
     */
    public function setEndVideoUrlAttribute($value)
    {
        $this->attributes['end_video_url'] = YoutubeVideo::formatUrl($value);
    }

    /**
     * Mutator for facebook_url.
     *
     * @param [type] $value
     * @return void
     */
    public function setFacebookUrlAttribute($value)
    {
        if (!empty($value) && !starts_with($value, ['http:', 'https:'])) {
            $this->attributes['facebook_url'] = 'https://' . $value;
        } else {
            $this->attributes['facebook_url'] = $value;
        }
    }

    /**
     * Mutator for instagram_url.
     *
     * @param [type] $value
     * @return void
     */
    public function setInstagramUrlAttribute($value)
    {
        if (!empty($value) && !starts_with($value, ['http:', 'https:'])) {
            $this->attributes['instagram_url'] = 'https://' . $value;
        } else {
            $this->attributes['instagram_url'] = $value;
        }
    }

    /**
     * Mutator for twitter_url.
     *
     * @param [type] $value
     * @return void
     */
    public function setTwitterUrlAttribute($value)
    {
        if (!empty($value) && !starts_with($value, ['http:', 'https:'])) {
            $this->attributes['twitter_url'] = 'https://' . $value;
        } else {
            $this->attributes['twitter_url'] = $value;
        }
    }

    /**
     * Returns the full qualified http path for the tour's intro audio.
     *
     * @return void
     */
    public function getIntroAudioPathAttribute()
    {
        if (empty($this->intro_audio)) {
            return null;
        }

        return config('filesystems.disks.s3.url') . $this->intro_audio;
    }

    /**
     * Returns the full qualified http path for the tour's background audio.
     *
     * @return void
     */
    public function getBackgroundAudioPathAttribute()
    {
        if (empty($this->background_audio)) {
            return null;
        }

        return config('filesystems.disks.s3.url') . $this->background_audio;
    }
}
