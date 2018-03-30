<?php

namespace App\Http\Requests\Cms;

use App\Tour;

class UpdateTourRequest extends CreateTourRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $isAdmin = auth()->user()->hasAnyRole(['admin', 'superadmin']);

        $isOwner = Tour::where('id', $this->route('tour')->id)
            ->where('user_id', auth()->user()->id)
            ->exists();

        return $isAdmin || $isOwner;
    }
}
