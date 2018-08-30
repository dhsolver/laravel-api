<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\DeviceType;
use App\Os;

class StoreDeviceRequest extends FormRequest
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
        $types = implode(',', DeviceType::all());
        $oses = implode(',', Os::all());

        return [
            'type' => "required|in:$types",
            'os' => "required|in:$oses",
            'device_udid' => 'required|string'
        ];
    }
}
