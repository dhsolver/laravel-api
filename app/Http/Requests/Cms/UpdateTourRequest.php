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
        return auth()->user()->ownsTour($this->route('tour')->id);
    }
}