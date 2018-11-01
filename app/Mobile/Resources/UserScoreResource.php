<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserScoreResource extends JsonResource
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
            'tour_id' => $this->tour_id,
            'tour_name' => $this->tour->title,
            'points' => (int) $this->points,
            'won_trophy' => $this->won_trophy,
            'trophy_url' => $this->won_trophy ? optional($this->tour->trophyImage)->path : null,
        ];
    }
}
