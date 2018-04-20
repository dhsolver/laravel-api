<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Tour;

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
        return [
            'title' => 'required|string|max:255|min:3',
            'description' => 'required|string|max:2000|min:3',
            'pricing_type' => [
                'required',
                Rule::in(Tour::$PRICING_TYPES),
            ],
            'type' => [
                'required',
                Rule::in(Tour::$TOUR_TYPES),
            ],

            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zipcode' => 'nullable|string|max:12',
            'facebook_url' => 'nullable|string',
            'twitter_url' => 'nullable|string',
            'instagram_url' => 'nullable|string',

            'video_url' => 'nullable|url|regex:/(youtube.com)/u',
            'start_video_url' => 'nullable|url|regex:/(youtube.com)/u',
            'end_video_url' => 'nullable|url|regex:/(youtube.com)/u',

            'start_message' => 'nullable|string|max:1000',
            'end_message' => 'nullable|string|max:1000',

            'has_prize' => 'nullable|boolean',
            'prize_details' => 'nullable|string|max:1000',
            'prize_instructions' => 'nullable|string|max:1000',

            'start_point' => [
                'nullable',
                'numeric',
                Rule::exists('tour_stops', 'id')->where(function ($query) {
                    $query->where('tour_id', $this->route('tour')->id);
                }),
            ],
            'end_point' => [
                'nullable',
                'numeric',
                Rule::exists('tour_stops', 'id')->where(function ($query) {
                    $query->where('tour_id', $this->route('tour')->id);
                }),
            ],
        ];
    }
}
