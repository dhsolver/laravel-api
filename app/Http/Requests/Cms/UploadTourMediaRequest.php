<?php

namespace App\Http\Requests\Cms;

use Illuminate\Foundation\Http\FormRequest;

class UploadTourMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->type->ownsTour(
            $this->route('tour')->id
        );
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
            'main_image' => "nullable|file|image|max:$max",
            'image_1' => "nullable|file|image|max:$max",
            'image_2' => "nullable|file|image|max:$max",
            'image_3' => "nullable|file|image|max:$max",
            'trophy_image' => "nullable|file|image|max:$max",
            'start_image' => "nullable|file|image|max:$max",
            'end_image' => "nullable|file|image|max:$max",

            'audio' => "nullable|file|mimetypes:audio/mpeg|max:$audioMax",
            'intro_audio' => "nullable|file|mimetypes:audio/mpeg|max:$audioMax",
            'background_audio' => "nullable|file|mimetypes:audio/mpeg|max:$audioMax",
        ];
    }
}
