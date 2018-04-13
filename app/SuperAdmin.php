<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\IsUserRole;

class SuperAdmin extends Model
{
    use IsUserRole;

    protected $with = [];

    protected $hidden = ['user'];

    protected $fillable = [];

    protected $table = 'super_admins';

    protected $dates = ['deleted_at'];
}
