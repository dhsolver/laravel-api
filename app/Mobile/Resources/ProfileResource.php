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
            'favorites' => $this->favorites->count(),
            'stats' => [
                'points' => $this->stats->points,
                'tours_completed' => $this->stats->tours_completed,
                'stops_visited' => $this->stats->stops_visited,
                'trophies' => $this->stats->trophies
            ]
        ];

        if ($this->id === auth()->user()->id) {
            $data['email'] = $this->email;
            $data['zipcode'] = $this->zipcode;
            $data['fb_id'] = $this->fb_id;
            $data['subscribe_override'] = in_array($this->role, ['admin', 'superadmin']) ? true : $this->subscribe_override;
        }

        return $data;
    }
}
