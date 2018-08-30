<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;

class Device extends Model
{
    use UsesUuid;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * A Device belongs to many Users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'user_devices');
    }

    /**
     * Check if the device unique identifier exists.
     *
     * @param string $udid
     * @return Device
     */
    public static function findByUdid($udid)
    {
        return self::where('device_udid', $udid)
            ->first();
    }
}
