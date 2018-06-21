<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StopRouteResource extends JsonResource
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
            'lat' => $this->resource->latitude,
            'lng' => $this->resource->longitude,
            'tour_id' => $this->resource->tour_id,
            'stop_id' => $this->resource->stop_id,
            'next_stop_id' => $this->resource->next_stop_id,
        ];
    }
}
