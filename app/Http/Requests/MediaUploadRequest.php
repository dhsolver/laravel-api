<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
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
        $max = config('junket.imaging.max_file_size');
        $audioMax = config('junket.audio.max_file_size');

        return [
            'image' => "nullable|file|image|max:$max",
            'audio' => "nullable|file|mimetypes:audio/mpeg|max:$audioMax",
        ];
    }
}
