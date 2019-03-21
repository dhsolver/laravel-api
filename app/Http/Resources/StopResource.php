<?php

namespace App\Http\Resources;

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
        $this->resource->load(['routes']);

        return array_merge($this->resource->toArray(), [
            'routes' => new StopRouteResource($this->resource->routes)
        ]);
    }
}
