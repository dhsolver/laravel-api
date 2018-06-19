<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\YoutubeVideo;

class CreateStopRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:16000',

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

            'next_stop_id' => [
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

    /**
     * Get the validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.*' => 'A Stop Title is required.',
            'title.max' => 'Stop title must be less than 255 characters.',
            'description.max' => 'Stop description is too long.',
            'location.latitude.*' => 'Invalid coordinates.',
            'location.longitude.*' => 'Invalid coordinates.',
            'location.*' => 'Invalid address.',
            'play_radius.*' => 'Location trigger must be numeric.',
            'question.max' => 'Question is too long.',
            'question_answer.max' => 'Question is too long.',
            'question_success.max' => 'Question is too long.',
            'choices.*.next_stop_id.*' => 'Next stop does not exist.',
            'video_url.*' => 'Invalid YouTube URL.',
            'main_image_id.*' => 'Feature Image file not found.',
            'image1_id.*' => 'Image 1 file not found.',
            'image2_id.*' => 'Image 2 file not found.',
            'image3_id.*' => 'Image 3 file not found.',
            'intro_audio_id.*' => 'Intro audio file not found.',
            'background_audio_id.*' => 'Intro audio file not found.',
        ];
    }
}
