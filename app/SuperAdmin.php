<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\IsUserRole;
use App\Traits\HasTours;

class SuperAdmin extends Model
{
    use IsUserRole, HasTours;

    protected $with = [];

    protected $hidden = ['user'];

    protected $fillable = [];

    protected $table = 'super_admins';

    protected $dates = ['deleted_at'];
}
