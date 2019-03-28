<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublishTourSubmission extends Model
{
    /**
     * The database table name.
     *
     * @var string
     */
    protected $table = 'publish_tour_submissions';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be converted to Carbon dates.
     *
     * @var array
     */
    protected $dates = ['approved_at', 'denied_at'];

    /**
     * Query scope to get only the pending submissions.
     *
     * @param Illuminate\Database\Query\Builder $query
     * @return Illuminate\Database\Query\Builder
     */
    public function scopePending($query)
    {
        return $query->whereNull('approved_at')
            ->whereNull('denied_at');
    }

    /**
     * Get the related tour.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tour()
    {
        return $this->belongsTo(\App\Tour::class);
    }

    /**
     * Approve the submission and publish the realted tour.
     *
     * @return void
     */
    public function approve()
    {
        $this->update([
            'approved_at' => \Carbon\Carbon::now(),
            'denied_at' => null
        ]);

        $this->tour->publish();
    }
}
