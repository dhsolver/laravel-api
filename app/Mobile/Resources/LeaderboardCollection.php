<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LeaderboardCollection extends ResourceCollection
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
            'leaders' => $this->collection->map(function ($item) {
                return [
                    'points' => $item->points,
                    'user' => new ProfileResource($item->user),
                ];
            })
        ];
    }
}
