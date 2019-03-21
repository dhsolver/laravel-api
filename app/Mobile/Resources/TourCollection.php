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
                return new TourResource($item);
            })
        ];
    }
}
