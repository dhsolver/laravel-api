<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TourProgressRequest extends FormRequest
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
            'stop_id' => 'required|exists:tour_stops,id',
            'timestamp' => 'required|date_format:U',
            'skipped_question' => 'nullable|boolean'
        ];
    }
}
