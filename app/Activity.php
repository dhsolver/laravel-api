<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    /**
     * The table name for the model.
     *
     * @var string
     */
    protected $table = 'activity';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get all of the owning actionable models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function actionable()
    {
        return $this->morphTo();
    }
}
