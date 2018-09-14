<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\IsUserRole;
use Tymon\JWTAuth\Contracts\JWTSubject;

class MobileUser extends Model implements JWTSubject
{
    use IsUserRole;

    protected $with = [];

    protected $hidden = ['user'];

    protected $fillable = [];

    protected $table = 'mobile_users';

    protected $dates = ['deleted_at'];
}
