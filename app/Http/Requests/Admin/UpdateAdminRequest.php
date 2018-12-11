<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->route('admin')->id,
            'zipcode' => 'nullable|string|max:16',
        ];
    }
}
