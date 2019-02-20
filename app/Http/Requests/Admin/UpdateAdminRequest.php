<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
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
     * Find the method name for the current route
     *
     * @return string
     */
    protected function findMethodName()
    {
        list($class, $method) = explode('@', $this->route()->getActionName());

        return $method;
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
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('admin')->id)->where(function($query) {
                    $query->where('user_type', 1);
                })
            ],
            'zipcode' => 'nullable|string|max:16',
        ];
    }
}
