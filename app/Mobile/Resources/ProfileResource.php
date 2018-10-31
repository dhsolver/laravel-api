<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Action;

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
            // 'role' => $this->role,
            'created_at' => $this->created_at->toDateTimeString(),
            'avatar_url' => $this->avatarUrl,
        ];

        if ($this->id === auth()->user()->id) {
            $data['email'] = $this->email;
            $data['fb_id'] = $this->fb_id;
            $data['subscribe_override'] = $this->subscribe_override;

            // UNDOCUMENTED::::::::
            $data['stats'] = [
                'points' => $this->scores()->finished()->sum('points'),
                'completed_tours' => $this->scores()->finished()->count(),
                'stops_visited' => $this->activity()->where('actionable_type', 'App\TourStop')->where('action', Action::START)->count(),
                'trophies' => $this->scores()->finished()->where('won_trophy', true)->count(),
            ];
        }

        return $data;
    }
}
