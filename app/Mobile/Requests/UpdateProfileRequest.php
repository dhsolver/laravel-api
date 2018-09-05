<?php

namespace App\Mobile\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(auth()->user()->id),
            ],
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
            'name.max' => 'Name must be less than 255 characters.',
            'name.*' => 'Your name is required.',
            'email.unique' => 'This email address is already in use by another user.',
            'email.max' => 'Email address must be less than 255 characters.',
            'email.*' => 'A valid email address is required.',
        ];
    }
}
