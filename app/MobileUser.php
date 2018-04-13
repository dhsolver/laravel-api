<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\IsUserRole;

class MobileUser extends Model
{
    use IsUserRole;

    protected $with = [];

    protected $hidden = ['user'];

    protected $fillable = [];

    protected $table = 'mobile_users';

    protected $dates = ['deleted_at'];
}
