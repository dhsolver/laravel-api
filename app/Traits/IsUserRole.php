<?php

namespace App\Traits;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

trait IsUserRole
{
    use SoftDeletes;

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
     * IsUserRole constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->alwaysIncludeUserRelationship();
        $this->appendAttributesToRoleModel();
    }

    /**
     * Add user model to with array
     *
     * @return void
     */
    protected function alwaysIncludeUserRelationship()
    {
        if (empty($this->with)) {
            $this->with = ['user'];
        }
    }

    /**
     * Append user attributes to the model.
     *
     * @return void
     */
    protected function appendAttributesToRoleModel()
    {
        $this->append(['name', 'email', 'role', 'fb_id', 'subscribe_override', 'avatar_url', 'zipcode', 'tour_limit', 'active']);
    }

    /**
     * Forward the magic getter to the related User model if property is not found in the Role model.
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        $parentValue = parent::__get($name);
        if ($parentValue === null) {
            if (isset($this->attributes[$this->primaryKey])) {
                return $this->user->$name ?? null;
            }
        }
        return $parentValue;
    }

    ///////////////////////////////////////////
    /// Related User
    ///////////////////////////////////////////

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    ///////////////////////////////////////////
    /// Attribute Input Handling
    ///////////////////////////////////////////

    /**
     * Simplifies the fill process to avoid checking against guarded attributes in the Role model
     * This is needed because $fillable is used to define role attributes which the rest being forwarded to the related User model
     *
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Overridden Save Method to save $fillable attributes to the Role Model with the remaining attributes forwarded to the related User Model
     *
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public function save(array $options = [])
    {
        $this->setIncrementing(false);

        $role_attributes = array_intersect_key($this->attributes, array_flip($this->fillable));
        $user_attributes = array_diff_key($this->attributes, array_flip($this->fillable));

        if ($this->id && (! isset($options['create']) || ! $options['create'])) {
            $user = $this->user;
            $user->update($user_attributes);
        } else {
            \DB::beginTransaction();

            $user = User::forceCreate(
                $user_attributes
            );

            $user->assignRole($this->getRoleType());
            \DB::commit();
        }

        if (! $user) {
            throw new \Exception('Unable to create user from role model.');
        }

        $this->attributes = array_merge(
            $role_attributes,
            ['id' => $user->id]
        );

        return parent::save($options);
    }

    /**
     * Get the name of this Role (e.g. App\Client returns Client)
     *
     * @return string
     */
    public function getRoleType()
    {
        $role = strtolower(class_basename(get_called_class()));
        return $role == 'mobileuser' ? 'user' : $role;
    }

    /**
     * Get the user name attribute.
     *
     * @return mixed
     */
    public function getNameAttribute()
    {
        return $this->user['name'];
    }

    /**
     * Get the user email attribute.
     *
     * @return mixed
     */
    public function getEmailAttribute()
    {
        return $this->user['email'];
    }

    /**
     * Get the user role attribute.
     *
     * @return mixed
     */
    public function getRoleAttribute()
    {
        return $this->user['role'];
    }

    /**
     * Get the user fb_id attribute.
     *
     * @return mixed
     */
    public function getFbIdAttribute()
    {
        return $this->user['fb_id'];
    }

    /**
     * Get the user subscribe_override attribute.
     *
     * @return mixed
     */
    public function getSubscribeOverrideAttribute()
    {
        return $this->user['subscribe_override'];
    }

    /**
     * Get the user avatar_url attribute.
     *
     * @return mixed
     */
    public function getAvatarUrlAttribute()
    {
        return $this->user['avatar_url'];
    }

    /**
     * Get the user zipcode attribute.
     *
     * @return mixed
     */
    public function getZipcodeAttribute()
    {
        return $this->user['zipcode'];
    }

    /**
     * Get the user tour_limit attribute.
     *
     * @return mixed
     */
    public function getTourLimitAttribute()
    {
        return $this->user['tour_limit'];
    }

    /**
     * Get the user active attribute.
     *
     * @return int
     */
    public function getActiveAttribute()
    {
        return $this->user['active'];
    }
}
