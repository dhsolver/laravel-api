<?php

namespace App\Console\Commands\ItourMobile\Models;

use Illuminate\Database\Eloquent\Model;
use MichaelAChrisco\ReadOnly\ReadOnlyTrait;

class UserAccount extends Model
{
    use ReadOnlyTrait;

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
    protected $table = 'user_accounts';

    public function getNameAttribute()
    {
        if (empty($this->firstName) || empty($this->lastName)) {
            return $this->username;
        }
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getCompanyAttribute()
    {
        if (empty($this->company_name)) {
            return null;
        }

        return $this->company_name;
    }
}
