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

            'is_multiple_choice' => $this->is_multiple_choice,
            'question' => $this->question,

            'question_answer' => $this->question_answer,
            'question_success' => $this->question_success,
            'next_stop' => $this->next_stop_id,

            'choices' => StopChoiceResource::collection($this->choices),

            'media' => [
                'image1' => new ImageResource($this->image1),
                'image2' => new ImageResource($this->image2),
                'image3' => new ImageResource($this->image3),
                'main_image' => new ImageResource($this->mainImage),
                'intro_audio' => new AudioResource($this->introAudio),
                'background_audio' => new AudioResource($this->backgroundAudio)
            ],

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,

            // 'routes' => new StopRouteResource($this->resource->routes),
        ];
    }
}
