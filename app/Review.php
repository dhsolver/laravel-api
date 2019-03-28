<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The custom attributes that are automatically appended to the model.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Relationships to always load.
     *
     * @var array
     */
    protected $with = [];

    /**
     * The attributes that should be specifically cast.
     *
     * @var array
     */
    protected $casts = [
        'rating' => 'int',
        'tour_id' => 'int',
        'user_id' => 'int'
    ];

    /**
     * Handles the model boot options.
     *
     * @return void
     */
    protected static function boot()
    {
        // update tour rating whenever a review is created
        static::saved(function ($model) {
            $rating = Review::where('tour_id', $model->tour->id)
                ->avg('rating');

            $model->tour->update(['rating' => intval($rating)]);
        });

        static::deleted(function ($model) {
            $rating = Review::where('tour_id', $model->tour->id)
                ->avg('rating');

            $model->tour->update(['rating' => intval($rating)]);
        });

        parent::boot();
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the related Tour.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Get the User that created the review.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Get the reviews by the given user.
     *
     * @param Illuminate\Database\Query\Builder $query
     * @param int $user_id
     * @return Illuminate\Database\Query\Builder
     */
    public function scopeByUser($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************
}
