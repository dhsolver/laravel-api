<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TourScoreCardCollection extends ResourceCollection
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
            'best' => new ScoreCardResource(
                $this->collection->where('finished_at', '!=', null)
                    ->sortByDesc('points')
                    ->first()
            ),
            'finished' => ScoreCardResource::collection(
                $this->collection->where('finished_at', '!=', null)
                    ->sortByDesc('finished_at')
            ),
            'in_progress' => ScoreCardResource::collection(
                $this->collection->where('finished_at', null)
                    ->sortByDesc('started_at')
            )
        ];
    }
}
