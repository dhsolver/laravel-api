<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
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
    protected $appends = ['path'];

    /**
     * Defines the user relationship for who uploaded the media.
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the media's full URL.
     *
     * @return void
     */
    public function getPathAttribute()
    {
        return config('filesystems.disks.s3.url') . $this->file;
    }
}
