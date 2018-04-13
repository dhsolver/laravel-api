<?php

namespace App\Traits;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

trait IsUserRole
{
    use SoftDeletes;

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

    protected function alwaysIncludeUserRelationship()
    {
        if (empty($this->with)) {
            $this->with = ['user'];
        }
    }

    protected function appendAttributesToRoleModel()
    {
        $this->append(['name', 'email', 'role']);
    }

    /**
     * Forward the magic getter to the related User model if property is not found in the Role model
     *
     * @param $name
     * @return null
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

        if ($this->id) {
            $user = $this->user;
            $user->update($user_attributes);
        } else {
            $user = User::forceCreate(
                $user_attributes
            );
            $user->assignRole($this->getRoleType());
        }

        if (!$user) {
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
        return $role === 'mobileuser' ? 'user' : $role;
    }

    public function getNameAttribute()
    {
        return $this->user->name;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    public function getRoleAttribute()
    {
        return $this->user->role;
    }
}
