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
                    'trophy_image' => $item->trophyImage ? $item->trophyImage->path : null,

                    'start_point' => $item->start_point_id,
                    'start_message' => $item->start_message,
                    'start_video_url' => $item->start_video_url,
                    'start_image' => $item->startImage ? $item->startImage->path : null,

                    'end_point' => $item->end_point_id,
                    'end_message' => $item->end_message,
                    'end_video_url' => $item->end_video_url,
                    'end_image' => $item->endImage ? $item->endImage->path : null,

                    'main_image' => $item->mainImage ? $item->mainImage->path : null,
                    'image1' => $item->image1 ? $item->image1->path : null,
                    'image2' => $item->image2 ? $item->image2->path : null,
                    'image3' => $item->image3 ? $item->image3->path : null,
                    'pin_image' => $item->pinImage ? $item->pinImage->path : null,

                    'intro_audio' => $item->introAudio ? $item->introAudio->path : null,
                    'background_audio' => $item->backgroundAudio ? $item->backgroundAudio->path : null,

                    'created_at' => $item->created_at->toDateTimeString(),
                    'updated_at' => $item->updated_at ? $item->updated_at->toDateTimeString() : null,
                    'published_at' => $item->published_at ? $item->published_at->toDateTimeString() : null,
                ];
            }),
        ];
    }
}
