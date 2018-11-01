<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPasswordNotification;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Str;
use Carbon\Carbon;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'fb_id', 'fb_token', 'subscribe_override', 'avatar'
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
    protected $appends = ['role', 'avatarUrl'];

    /**
     * Set the guard name for the JWTSubject.
     *
     * @var string
     */
    protected $guard_name = 'api';

    /**
     * The attributes that should be specifically cast.
     *
     * @var array
     */
    protected $casts = ['subscribe_override' => 'bool'];

    /**
     * The attributes that should be cast to dates.
     *
     * @var array
     */
    protected $dates = ['email_confirmed_at'];

    /**
     * Handles the model boot options.
     *
     * @return void
     */
    public static function boot()
    {
        // create email confirmation token on creation
        self::creating(function ($model) {
            $model->email_confirmation_token = Str::random(64);
        });

        // always create a user stats table
        self::created(function ($model) {
            $model->stats()->create();
        });

        parent::boot();
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the user stats relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function stats()
    {
        return $this->hasOne(UserStats::class);
    }

    /**
     * Get the user's uploaded media relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function media()
    {
        return $this->hasMany(Media::class);
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
     * Get the user's score cards relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function scoreCards()
    {
        return $this->hasMany(ScoreCard::class);
    }

    /**
     * A User has many activity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function activity()
    {
        return $this->hasMany(Activity::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Gets the users role
     *
     * @return String
     */
    public function getRoleAttribute()
    {
        return $this->roles()->pluck('name')->first();
    }

    public function getFirstNameAttribute()
    {
        $names = explode(' ', $this->name, 2);

        return $names[0];
    }

    public function getLastNameAttribute()
    {
        $names = explode(' ', $this->name, 2);

        if (isset($names[1]) && ! empty($names[1])) {
            return $names[1];
        }

        return '';
    }

    /**
     * Get the URL for the user's avatar.
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        if (empty($this->avatar)) {
            return config('filesystems.disks.s3.url') . 'default_user_icon.png';
        }

        return config('filesystems.disks.s3.url') . 'avatars/' . $this->avatar;
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // STATIC METHODS
    // **********************************************************

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

    // **********************************************************
    // OTHER METHODS
    // **********************************************************

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
        if (! $type) {
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

    /**
     * Look up the user by the confirmation token and set to confirmed
     *
     * @param string $token
     * @return bool|\App\User
     */
    public static function confirmEmail($token)
    {
        $user = self::where('email_confirmation_token', $token)->first();

        if (empty($user)) {
            return false;
        }

        if (empty($user->email_confirmed_at)) {
            $user->email_confirmed_at = Carbon::now();
            $user->save();
        }

        return $user;
    }

    /**
     * Get the user's total score.
     *
     * @return int
     */
    public function getScore()
    {
        $points = $this->scoreCards()->finished()->sum('points');

        if (empty($points)) {
            return 0;
        }

        return (int) $points;
    }
}
