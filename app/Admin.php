<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\IsUserRole;

class Admin extends Model
{
    use IsUserRole;

    protected $with = [];

    protected $hidden = ['user'];

    protected $fillable = [];

    protected $table = 'admins';

    protected $dates = ['deleted_at'];
}
