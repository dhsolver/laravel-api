<?php

namespace App\Http\Resources;

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
        $this->resource->load(['stops', 'route']);

        return array_merge($this->resource->toArray(), [
            'route' => RouteResource::collection($this->resource->route),
            'stops' => StopResource::collection($this->resource->stops),
        ]);
    }
}
