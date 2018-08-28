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
            'icon' => "nullable|file|image|max:$max|mimetypes:image/png",
            'audio' => "nullable|file|mimetypes:audio/mpeg|max:$audioMax",
        ];
    }

    public function messages()
    {
        $max = config('junket.imaging.max_file_size');
        $audioMax = config('junket.audio.max_file_size');

        return [
            'image.max' => 'Image must be less than ' . $max . ' KB',
            'image.*' => 'Image must be a valid image file.',
            'icon.max' => 'Image must be less than ' . $max . ' KB',
            'icon.*' => 'Image must be a valid PNG file.',
            'audio.max' => 'Audio file must be less than ' . $audioMax . ' KB',
            'audio.*' => 'Audio file must be a valid MP3 file.',
        ];
    }
}
