<?php

namespace App\Mobile\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReviewRequest extends FormRequest
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
        return [
            'rating' => 'in:0,5,10,15,20,25,30,35,40,45,50',
            'review' => 'nullable|max:255'
        ];
    }

    /**
     * Get the validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'review.max' => 'Review must be less than 255 characters.',
            'rating.*' => 'Invalid rating value.'
        ];
    }
}
