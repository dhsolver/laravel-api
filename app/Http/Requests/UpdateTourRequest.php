<?php

namespace App\Http\Requests;

use App\TourType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Tour;
use App\Rules\YoutubeVideo;

class UpdateTourRequest extends FormRequest
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
        $rules = [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3',
                Rule::unique('tours', 'title')->ignore(request()->route('tour')),
            ],
            'description' => 'nullable|max:16000|min:3',
            'pricing_type' => [
                'required',
                Rule::in(Tour::$PRICING_TYPES),
            ],
            'type' => [
                'required',
                Rule::in(TourType::all()),
            ],

            'location.address1' => 'nullable|string|max:255',
            'location.address2' => 'nullable|string|max:255',
            'location.city' => 'nullable|string|max:155',
            'location.state' => 'nullable|string|max:155',
            'location.country' => 'nullable|string|max:2',
            'location.zipcode' => 'nullable|string|max:12',
            'location.latitude' => ['nullable', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'location.longitude' => ['nullable', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],

            'facebook_url' => 'nullable|string|regex:/(facebook.com\/[a-zA-Z0-9])/u',
            'twitter_url' => 'nullable|string|regex:/(twitter.com\/[a-zA-Z0-9])/u',
            'instagram_url' => 'nullable|string|regex:/(instagram.com\/[a-zA-Z0-9])/u',

            'video_url' => ['nullable', 'url', new YoutubeVideo],
            'start_video_url' => ['nullable', 'url', new YoutubeVideo],
            'end_video_url' => ['nullable', 'url', new YoutubeVideo],

            'start_message' => 'nullable|string|max:1000',
            'end_message' => 'nullable|string|max:1000',

            'has_prize' => 'nullable|boolean',
            'prize_details' => 'nullable|string|max:1000',
            'prize_instructions' => 'nullable|string|max:1000',
            'prize_time_limit' => 'nullable|numeric',

            'start_point_id' => [
                'nullable',
                'numeric',
                Rule::exists('tour_stops', 'id')->where(function ($query) {
                    $query->where('tour_id', $this->route('tour')->id);
                }),
            ],
            'end_point_id' => [
                'nullable',
                'numeric',
                Rule::exists('tour_stops', 'id')->where(function ($query) {
                    $query->where('tour_id', $this->route('tour')->id);
                }),
            ],

            'main_image_id' => 'nullable|integer|exists:media,id',
            'start_image_id' => 'nullable|integer|exists:media,id',
            'end_image_id' => 'nullable|integer|exists:media,id',
            'pin_image_id' => 'nullable|integer|exists:media,id',
            'image1_id' => 'nullable|integer|exists:media,id',
            'image2_id' => 'nullable|integer|exists:media,id',
            'image3_id' => 'nullable|integer|exists:media,id',
            'trophy_image_id' => 'nullable|integer|exists:media,id',
            'intro_audio_id' => 'nullable|integer|exists:media,id',
            'background_audio_id' => 'nullable|integer|exists:media,id',

            'route' => 'nullable',
        ];

        if (auth()->user()->isAdmin()) {
            $rules['in_app_id'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    /**
     * Get the validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.unique' => 'A Tour with this name already exists.',
            'title.max' => 'Tour title must be less than 255 characters.',
            'title.*' => 'Tour title is required.',
            'description.max' => 'Tour description is too long.',
            'description.*' => 'Tour description is required.',
            'pricing_type.*' => 'Tour pricing type must be selected.',
            'type.*' => 'Tour type must be selected.',

            'location.latitude.*' => 'Invalid coordinates.',
            'location.longitude.*' => 'Invalid coordinates.',
            'location.*' => 'Invalid address.',

            'facebook_url.*' => 'Invalid Facebook URL.',
            'twitter_url.*' => 'Invalid Twitter URL.',
            'instagram_url.*' => 'Invalid Instagram URL.',
            'video_url.*' => 'Invalid YouTube URL.',
            'start_video_url.*' => 'Invalid YouTube URL.',
            'end_video_url.*' => 'Invalid YouTube URL.',
            'start_message.max' => 'Starting point message is too long.',
            'end_message.max' => 'Starting point message is too long.',
            'prize_details.max' => 'Prize details are too long.',
            'prize_instructions.max' => 'Prize instructions are too long.',
            'start_point_id.*' => 'Starting point does not exist.',
            'end_point_id.*' => 'End point does not exist.',

            'main_image_id.*' => 'Feature Image file not found.',
            'image1_id.*' => 'Image 1 file not found.',
            'image2_id.*' => 'Image 2 file not found.',
            'image3_id.*' => 'Image 3 file not found.',
            'intro_audio_id.*' => 'Intro audio file not found.',
            'background_audio_id.*' => 'Intro audio file not found.',
            'trophy_image_id.*' => 'Trophy Image file not found.',
            'start_image_id.*' => 'Starting point image file not found.',
            'end_image_id.*' => 'End point image file not found.',
            'pin_image_id.*' => 'Custom pin image file not found.',
        ];
    }
}
