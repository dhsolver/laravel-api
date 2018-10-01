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
            // 'role' => $this->role,
            'created_at' => $this->created_at->toDateTimeString(),
            'avatar_url' => $this->avatarUrl,
        ];

        if ($this->id === auth()->user()->id) {
            $data['email'] = $this->email;
            $data['fb_id'] = $this->fb_id;
            $data['subscribe_override'] = $this->subscribe_override;
        }

        return $data;
    }
}
