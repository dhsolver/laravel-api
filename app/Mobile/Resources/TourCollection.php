<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TourCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'total' => count($this->collection),
            'status' => 1,
            'data' => $this->collection->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'pricing_type' => $item->pricing_type,
                    'type' => $item->type,
                    'stops_count' => $item->stops_count,
                    'location' => new LocationResource($item->location),

                    'facebook_url' => $item->facebook_url,
                    'twitter_url' => $item->twitter_url,
                    'instagram_url' => $item->instagram_url,

                    'video_url' => $item->video_url,

                    'has_prize' => $item->has_prize,
                    'prize_details' => $item->prize_details,
                    'prize_instructions' => $item->prize_instructions,

                    'start_point' => $this->start_point_id,
                    'start_message' => $item->start_message,
                    'start_video_url' => $item->start_video_url,

                    'end_point' => $this->end_point_id,
                    'end_message' => $item->end_message,
                    'end_video_url' => $item->end_video_url,

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

                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'published_at' => $item->published_at,
                ];
            }),
        ];
        // return parent::toArray($request);
    }
}
