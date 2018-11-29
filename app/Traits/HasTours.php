<?php

namespace App\Traits;

use App\Tour;

trait HasTours
{
    /**
     * A user has many tours
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tours()
    {
        return $this->hasMany(Tour::class, 'user_id', 'id');
    }

    /**
     * Determines if the user owns the given Tour id.
     *
     * @param int $tourId
     * @return bool
     */
    public function ownsTour($tourId)
    {
        return Tour::where('id', $tourId)
            ->where('user_id', $this->attributes['id'])
            ->exists();
    }
}
