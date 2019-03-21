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
        $routes = [];
        foreach ($this->groupByNextStop() as $key => $val) {
            array_push($routes, [
                'next_stop_id' => $key,
                'route' => $val
            ]);
        }

        return $routes;
    }

    /**
     * Returns associative array of all routes mapping
     * next_stop_id => [routes array]
     *
     * @return array
     */
    public function groupByNextStop()
    {
        $routes = [];

        foreach ($this->resource as $item) {
            $route = [
                'lat' => $item->latitude,
                'lng' => $item->longitude,
            ];

            if (isset($routes[$item->next_stop_id])) {
                array_push($routes[$item->next_stop_id], $route);
            } else {
                $routes[$item->next_stop_id] = [$route];
            }
        }

        return $routes;
    }
}
