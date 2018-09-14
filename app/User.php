<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPasswordNotification;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'fb_id', 'fb_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be added for arrays.
     *
     * @var array
     */
    protected $appends = ['role'];

    /**
     * Set the guard name for the JWTSubject.
     *
     * @var string
     */
    protected $guard_name = 'api';

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

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
     * Check if the User is an Admin.
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->role === 'admin' || $this->role === 'superadmin';
    }

    /**
     * Return the fully-qualified name of the role class
     *
     * @param null $type
     * @return null|string
     */
    public function getRoleClassObj($type = null)
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
        if ($this->getRoleClassObj()) {
            return $this->hasOne($this->getRoleClassObj(), 'id', 'id');
        }
        return null;
    }

    /**
     * Lookup User by their Facebook ID
     *
     * @param string $fbId
     * @return mixed
     */
    public static function findByFacebookId($fbId)
    {
        return self::where('fb_id', $fbId)->first();
    }

    /**
     * Lookup User by their Facebook ID
     *
     * @param string $email
     * @return mixed
     */
    public static function findByEmail($email)
    {
        return self::where('email', strtolower($email))->first();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token, $this->email));
    }

    /**
     * A User has many Devices.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function devices()
    {
        return $this->belongsToMany(Device::class, 'user_devices');
    }

    /**
     * Get the User's joined tours relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function joinedTours()
    {
        return $this->belongsToMany(Tour::class, 'user_joined_tours');
    }

    /**
     * Check whether the User has already joined the given Tour.
     *
     * @param array|object|int $tour
     * @return boolean
     */
    public function hasJoinedTour($tour)
    {
        $tour = is_object($tour) ? $tour->id : is_array($tour) ? $tour['id'] : $tour;

        return $this->joinedTours()->where('tour_id', $tour)->exists();
    }

    /**
     * Add user to the given Tour.
     *
     * @param object|int $tour
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function joinTour($tour)
    {
        if (is_numeric($tour)) {
            $tour = Tour::findOrFail($tour);
        }

        $this->joinedTours()->attach($tour);
    }
}
