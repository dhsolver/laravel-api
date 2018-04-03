<?php

namespace App\Http\Requests\Cms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Tour;

class UpdateTourRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255|min:3',
            'description' => 'required|string|max:2000|min:3',
            'pricing_type' => [
                'required',
                Rule::in(Tour::$PRICING_TYPES),
            ],
            'type' => [
                'required',
                Rule::in(Tour::$TOUR_TYPES),
            ],

            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zipcode' => 'nullable|string|max:12',
            'facebook_url' => 'nullable|string',
            'twitter_url' => 'nullable|string',
            'instagram_url' => 'nullable|string',
        ];
    }
}
