<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
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
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
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
            'name.max' => 'Your name is too long.',
            'email.unique' => 'A user with that email already exists.',
            'email.*' => 'A valid email is required.',
            'password.min' => 'Your password must be at least 6 characters.',
            'password.confirmed' => 'Passwords did not match.',
            'password.*' => 'Your password is required.',
        ];
    }
}
