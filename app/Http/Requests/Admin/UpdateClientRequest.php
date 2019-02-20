<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
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
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('client')->id)->where(function($query) {
                    $query->where('user_type', 1);
                })
            ],
            'zipcode' => 'nullable|string|max:16',
            'tour_limit' => 'required|integer',
            'subscribe_override' => 'nullable|boolean',
        ];
    }
}
