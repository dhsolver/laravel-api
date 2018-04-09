<?php

namespace App\Http\Requests\Cms;

use Illuminate\Foundation\Http\FormRequest;
use App\TourStop;
use Illuminate\Validation\Rule;

class UpdateStopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->ownsTour(
            $this->route('tour')->id
        );
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
            'location_type' => [
                'required',
                Rule::in(TourStop::$LOCATION_TYPES),
            ],

            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zipcode' => 'nullable|string|max:12',

            'latitude' => ['nullable', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'longitude' => ['nullable', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],

            'question' => 'nullable|string|max:500',
            'question_answer' => 'nullable|string|max:500',
            'question_success' => 'nullable|string|max:500',

            'is_multiple_choice' => 'nullable|boolean',
        ];
    }
}
