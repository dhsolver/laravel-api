<?php

namespace App\Http\Requests;

use Illuminate\Database\Eloquent\Model;
use App\Events\ChangeEmailRequestCreated;

class ChangeEmailRequest extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be converted to Carbon dates.
     *
     * @var array
     */
    protected $dates = ['expires_at', 'confirmed_at'];

    /**
     * The events to dispatch.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => ChangeEmailRequestCreated::class,
    ];

    /**
     * Get the related user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the activation code with a dash in the middle.
     *
     * @return string
     */
    public function getReadableCodeAttribute()
    {
        return substr($this->activation_code, 0, 3) . '-' . substr($this->activation_code, 3);
    }
}
