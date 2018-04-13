<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\IsUserRole;
use App\Traits\HasTours;

class Admin extends Model
{
    use IsUserRole, HasTours;

    protected $with = [];

    protected $hidden = ['user'];

    protected $fillable = [];

    protected $table = 'admins';

    protected $dates = ['deleted_at'];
}
