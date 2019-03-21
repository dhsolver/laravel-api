<?php

namespace App\Http\Requests;

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
            'company_name' => 'nullable|string|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore(auth()->user()->id)->where(function($query) {
                    $query->where('user_type', 1);
                })
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
            'company_name.max' => 'Company name must be less than 100 characters.',
            'name.*' => 'Your name is required.',
            'company_name.*' => 'Invalid company name.',
            'email.unique' => 'This email address is already in use by another user.',
            'email.max' => 'Email address must be less than 255 characters.',
            'email.*' => 'A valid email address is required.'
        ];
    }
}
