<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TourResource extends JsonResource
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
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'pricing_type' => $this->pricing_type,
            'type' => $this->type,
            'stops_count' => $this->stops_count,
            'location' => new LocationResource($this->location),

            'facebook_url' => $this->facebook_url,
            'twitter_url' => $this->twitter_url,
            'instagram_url' => $this->instagram_url,

            'video_url' => $this->video_url,

            'has_prize' => $this->has_prize,
            'prize_details' => $this->prize_details,
            'prize_instructions' => $this->prize_instructions,

            'start_point' => $this->start_point_id,
            'start_message' => $this->start_message,
            'start_video_url' => $this->start_video_url,

            'end_point' => $this->end_point_id,
            'end_message' => $this->end_message,
            'end_video_url' => $this->end_video_url,

            'media' => [
                'image1' => new ImageResource($this->image1),
                'image2' => new ImageResource($this->image2),
                'image3' => new ImageResource($this->image3),
                'main_image' => new ImageResource($this->mainImage),
                'start_image' => new ImageResource($this->startImage),
                'end_image' => new ImageResource($this->endImage),
                'pin_image' => new IconResource($this->pinImage),
                'trophy_image' => new IconResource($this->trophyImage),
                'intro_audio' => new AudioResource($this->introAudio),
                'background_audio' => new AudioResource($this->backgroundAudio)
            ],

            'route' => TourRouteResource::collection($this->route),
            'stops' => StopResource::collection($this->resource->stops),

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'published_at' => $this->published_at ? $this->published_at->toDateTimeString() : null,
        ];
    }
}
