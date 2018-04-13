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
     * Gets the users role
     *
     * @return String
     */
    public function getRoleAttribute()
    {
        return $this->roles()->pluck('name')->first();
    }

    /**
     * Return the fully-qualified name of the role class
     *
     * @param null $type
     * @return null|string
     */
    public function getRoleClass($type = null)
    {
        if (!$type) {
            $type = $this->role;
        }

        switch ($type) {
            case 'admin':
                return Admin::class;
            case 'user':
                return MobileUser::class;
            case 'client':
                return Client::class;
            case 'superadmin':
                return SuperAdmin::class;
        }

        return null;
    }

    /**
     * Get the role user object.
     *
     * @return void
     */
    public function type()
    {
        if ($this->getRoleClass()) {
            return $this->hasOne($this->getRoleClass(), 'id', 'id');
        }
        return null;
    }
}
