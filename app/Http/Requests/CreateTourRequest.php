<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Tour;
use Illuminate\Validation\Rule;

class CreateTourRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3',
                Rule::unique('tours', 'title'),
            ],
            'description' => 'nullable|max:16000|min:3',
            'pricing_type' => [
                'required',
                Rule::in(Tour::$PRICING_TYPES),
            ],
            'type' => [
                'required',
                Rule::in(Tour::$TOUR_TYPES),
            ],
        ];

        if ($this->route()->getPrefix() == 'admin') {
            $rules['user_id'] = 'required|numeric|exists:clients,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'title.unique' => 'A Tour with this name already exists.',
            'title.max' => 'Tour title must be less than 255 characters.',
            'title.*' => 'Tour title is required.',
            'description.max' => 'Tour description is too long.',
            'pricing_type.*' => 'Tour pricing type must be selected.',
            'type.*' => 'Tour type must be selected.',
        ];
    }
}
