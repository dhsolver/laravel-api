<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TourRouteResource extends JsonResource
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
            'order' => $this->order,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }
}
