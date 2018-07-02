<?php

namespace App\Console\Commands\ItourMobile\Models;

use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class Tour extends Model
{
    use ReadOnlyTrait;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'itourmobile';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tours';
}
