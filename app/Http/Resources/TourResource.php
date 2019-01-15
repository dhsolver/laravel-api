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
        $data = $this->resource->toArray();
        if (isset($this->resource->relationsp['stops'])) {
            $data['stops'] = StopResource::collection($this->resource->stops);
        }

        if (isset($this->resource->relationsp['route'])) {
            $data['route'] = RouteResource::collection($this->resource->route);
        }

        return $data;
    }
}
