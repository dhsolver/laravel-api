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

    /**
     * Get all activity since timestamp.
     *
     * @param \Illuminate\Database\Query\Builder query
     * @param \Carbon\Carbon $timestamp
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSince($query, $timestamp)
    {
        if (! empty($timestamp)) {
            return $query->where('created_at', '>=', $timestamp);
        }

        return $query;
    }
}
