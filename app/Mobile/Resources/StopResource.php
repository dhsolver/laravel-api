<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tour_id' => $this->tour_id,
            'order' => $this->order,
            'title' => $this->title,
            'description' => $this->description,
            'play_radius' => $this->play_radius,
            'video_url' => $this->video_url,
            'location' => new LocationResource($this->location),

            'main_image' => $this->mainImage ? $this->mainImage->path : null,
            'image1' => $this->image1 ? $this->image1->path : null,
            'image2' => $this->image2 ? $this->image2->path : null,
            'image3' => $this->image3 ? $this->image3->path : null,
            'intro_audio' => $this->introAudio ? $this->introAudio->path : null,
            'background_audio' => $this->backgroundAudio ? $this->backgroundAudio->path : null,

            'is_multiple_choice' => $this->is_multiple_choice,
            'question' => $this->question,

            'question_answer' => $this->question_answer,
            'question_success' => $this->question_success,
            'next_stop' => $this->next_stop_id,

            'choices' => StopChoiceResource::collection($this->choices),
            'routes' => StopRouteResource::collection($this->routes),

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
