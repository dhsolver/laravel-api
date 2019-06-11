<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Action;

class RecordActivityRequest extends FormRequest
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
            'activity' => 'array',
            'activity.*.device_id' => 'required|exists:devices,id',
            'activity.*.action' => 'required|in:' . implode(',', Action::all()),
            'activity.*.begin_timestamp' => 'required|date_format:U',
            'activity.*.end_timestamp' => 'required|date_format:U',
        ];
    }
}
