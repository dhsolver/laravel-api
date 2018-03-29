<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = ['role'];

    /**
     * A user has many tours
     *
     * @return void
     */
    public function tours()
    {
        return $this->hasMany(Tour::class);
    }

    /**
     * Gets the users role
     */
    public function getRoleAttribute()
    {
        return $this->roles()->pluck('name')->first();
    }
}
