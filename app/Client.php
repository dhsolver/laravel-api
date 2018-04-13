<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\IsUserRole;

class Client extends Model
{
    use IsUserRole;

    protected $with = [];

    protected $hidden = ['user'];

    protected $fillable = ['company_name'];

    protected $table = 'clients';

    protected $dates = ['deleted_at'];
}
