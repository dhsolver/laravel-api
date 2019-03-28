<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_ICON = 'icon';
    public const TYPE_AUDIO = 'audio';

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
    protected $appends = ['path', 'small_path', 'icon_path'];

    /**
     * The attributes that should be automatically cast.
     *
     * @var array
     */
    protected $casts = [
        'length' => 'float'
    ];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Defines the user relationship for who uploaded the media.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get the media's full URL.
     *
     * @return string
     */
    public function getPathAttribute()
    {
        return config('filesystems.disks.s3.url') . $this->file;
    }

    /**
     * Get the media's full URL.
     *
     * @return string
     */
    public function getSmallPathAttribute()
    {
        return config('filesystems.disks.s3.url') . $this->modFilename($this->file, '_sm');
    }

    /**
     * Get the media's full URL.
     *
     * @return string
     */
    public function getIconPathAttribute()
    {
        return config('filesystems.disks.s3.url') . $this->modFilename($this->file, '_ico');
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Add string to the end of the filename.
     *
     * @param string $filename
     * @param string $mod
     * @return string
     */
    public function modFilename($filename, $mod)
    {
        return substr($filename, 0, strpos($filename, '.')) . $mod . substr($filename, strpos($filename, '.'));
    }
}
