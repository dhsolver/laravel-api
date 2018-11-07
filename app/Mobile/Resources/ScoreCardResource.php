<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScoreCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->won_trophy_at && $this->tour->has_prize) {
            $prize = [
                'details' => $this->tour->prize_details,
                'instructions' => $this->tour->prize_instructions,
                'time_limit' => $this->tour->prize_time_limit,
                'expires_at' => $this->prize_expires_at->toDateTimeString(),
            ];
        }

        return [
            'tour_id' => $this->tour_id,
            'tour_name' => $this->tour->title,
            'stops_visited' => $this->stops_visited,
            'total_stops' => $this->total_stops,
            'points' => (int) $this->points,
            'won_trophy' => $this->won_trophy,
            'trophy_url' => $this->won_trophy ? optional($this->tour->trophyImage)->path : null,
            'prize' => $prize ?? null,
            'started_at' => $this->started_at->toDateTimeString(),
            'finished_at' => optional($this->finished_at)->toDateTimeString(),
        ];
    }
}
