<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\ScoreCard;

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
                'time_limit' => (int) $this->tour->prize_time_limit,
                'expires_at' => $this->prize_expires_at->toDateTimeString(),
                'prize_location' => new LocationResource($this->tour->prizeLocation)
            ];
        }
        
        $visited_stops = ScoreCard::find($this->id)->stops()->get();
        $visited_stop_ids = [];
        foreach ($visited_stops as $visited_stop) {
            $visited_stop_ids[] = $visited_stop['id'];
        }

        return [
            'id' => $this->id,
            'par' => $this->par,
            'is_adventure' => $this->is_adventure,
            'stops_visited' => $visited_stop_ids,
            'total_stops' => (int) $this->total_stops,
            'points' => (int) $this->points,
            'won_trophy' => $this->won_trophy,
            'trophy_url' => $this->won_trophy ? optional($this->tour->trophyImage)->path : null,
            'prize' => $prize ?? null,
            'started_at' => $this->started_at->toDateTimeString(),
            'finished_at' => optional($this->finished_at)->toDateTimeString(),
            'tour_id' => $this->tour_id
        ];
    }
}
