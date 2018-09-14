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
            'name' => $this->name,
            'role' => $this->role,
            'created_at' => $this->created_at->toDateTimeString(),
        ];

        if ($this->id === auth()->user()->id) {
            $data['email'] = $this->email;
        }

        return $data;
    }
}