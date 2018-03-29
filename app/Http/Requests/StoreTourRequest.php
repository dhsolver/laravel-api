<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Tour;
use Illuminate\Validation\Rule;

class StoreTourRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->hasAnyRole(['business', 'admin', 'superadmin']);
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
        ];
    }
}
