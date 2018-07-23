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

                    'start_point' => $item->start_point_id,
                    'start_message' => $item->start_message,
                    'start_video_url' => $item->start_video_url,

                    'end_point' => $item->end_point_id,
                    'end_message' => $item->end_message,
                    'end_video_url' => $item->end_video_url,

                    'media' => [
                        'image1' => $item->image1 ? new ImageResource($item->image1) : null,
                        'image2' => $item->image2 ? new ImageResource($item->image2) : null,
                        'image3' => $item->image3 ? new ImageResource($item->image3) : null,
                        'main_image' => $item->mainImage ? new ImageResource($item->mainImage) : null,
                        'start_image' => $item->startImage ? new ImageResource($item->startImage) : null,
                        'end_image' => $item->endImage ? new ImageResource($item->endImage) : null,
                        'pin_image' => $item->pinImage ? new IconResource($item->pinImage) : null,
                        'trophy_image' => $item->trophyImage ? new IconResource($item->trophyImage) : null,
                        'intro_audio' => $item->introAudio ? new AudioResource($item->introAudio) : null,
                        'background_audio' => $item->backgroundAudio ? new AudioResource($item->backgroundAudio) : null,
                    ],

                    'created_at' => $item->created_at->toDateTimeString(),
                    'updated_at' => $item->updated_at ? $item->updated_at->toDateTimeString() : null,
                    'published_at' => $item->published_at ? $item->published_at->toDateTimeString() : null,
                ];
            }),
        ];
    }
}
