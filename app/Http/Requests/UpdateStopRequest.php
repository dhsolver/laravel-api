<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\YoutubeVideo;

class UpdateStopRequest extends FormRequest
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
            'title' => 'required|string|max:255|min:3',
            'description' => 'required|string|max:16000|min:3',

            'location.address1' => 'nullable|string|max:255',
            'location.address2' => 'nullable|string|max:255',
            'location.city' => 'nullable|string|max:100',
            'location.state' => 'nullable|string|max:2',
            'location.zipcode' => 'nullable|string|max:12',
            'location.latitude' => ['nullable', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'location.longitude' => ['nullable', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],

            'play_radius' => 'nullable|numeric|min:0',

            'question' => 'nullable|string|max:500',
            'question_answer' => 'nullable|string|max:500',
            'question_success' => 'nullable|string|max:500',

            'is_multiple_choice' => 'nullable|boolean',

            'choices' => 'nullable|array',
            'choices.*.answer' => 'required|string',
            'choices.*.next_stop_id' => [
                'nullable',
                'numeric',
                Rule::exists('tour_stops', 'id')->where(function ($query) {
                    $query->where('tour_id', $this->route('tour')->id)
                        ->where('id', '<>', $this->route('stop')->id);
                }),
            ],

            'video_url' => ['nullable', 'url', new YoutubeVideo],
            'main_image_id' => 'nullable|integer|exists:media,id',
            'image1_id' => 'nullable|integer|exists:media,id',
            'image2_id' => 'nullable|integer|exists:media,id',
            'image3_id' => 'nullable|integer|exists:media,id',
            'intro_audio_id' => 'nullable|integer|exists:media,id',
            'background_audio_id' => 'nullable|integer|exists:media,id',
        ];
    }
}
