<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\IsUserRole;
use App\Traits\HasTours;

class Client extends Model
{
    use IsUserRole, HasTours;

    protected $with = [];

    protected $hidden = ['user'];

    protected $fillable = ['company_name'];

    protected $table = 'clients';

    protected $dates = ['deleted_at'];
}
