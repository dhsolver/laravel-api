<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'avatar_url' => $this->avatarUrl,
            'created_at' => $this->created_at->toDateTimeString(),
        ];

        if ($this->id === auth()->user()->id) {
            $data['email'] = $this->email;
            $data['zipcode'] = $this->zipcode;
            $data['fb_id'] = $this->fb_id;
            $data['subscribe_override'] = $this->subscribe_override;

            $data['stats'] = [
                'points' => $this->stats->points,
                'tours_completed' => $this->stats->points,
                'stops_visited' => $this->stats->stops_visited,
                'trophies' => $this->stats->trophies,
            ];
        }

        return $data;
    }
}
