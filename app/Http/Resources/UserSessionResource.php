<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSessionResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'zipcode' => $this->zipcode,
            'fb_id' => $this->fb_id,
            'role' => $this->role,
            'avatar_url' => $this->avatarUrl,
            'subscribe_override' => in_array($this->role, ['admin', 'superadmin']) ? true : $this->subscribe_override,
            'created_at' => $this->created_at->toDateTimeString()
        ];
    }
}
