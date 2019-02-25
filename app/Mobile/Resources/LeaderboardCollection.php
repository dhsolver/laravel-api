<?php

namespace App\Mobile\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LeaderboardCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return [
        //     'leaders' => $this->collection->sortByDesc('points')->map(function ($item) {
        //         return [
        //             'points' => (int) $item->points,
        //             'user' => new ProfileResource($item->user),
        //         ];
        //     })
        // ];
        $leaders = [];
        $i = 1;
        foreach ($this->collection->sortByDesc('points') as $leader) {
            if ($i >= 100) continue;
            $leaders[] = [
                'points' => (int) $leader->points,
                'user' => new ProfileResource($leader->user),
                'user_rank' => $i,
                'total_users' => count($this->collection->sortByDesc('points'))
            ];
            $i ++;
        }
        return [
            'leaders' => $leaders
        ];
    }
}
