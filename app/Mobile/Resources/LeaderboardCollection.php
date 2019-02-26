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
        $collection = $this->collection->sortByDesc('points');
        $user_rank = 0;
        $current_user_id = auth()->user()->id;
        foreach ($collection as $leader) {
            $user = new ProfileResource($leader->user);
            $leaders[] = [
                'points' => (int) $leader->points,
                'user' => $user
            ];
            if ($user->id == $current_user_id) {
                $user_rank = $i;
            }
            $i ++;
        }
        return [
            'leaders' => array_splice($leaders, 0, 100),
            'total_users' => count($collection),
            'user_rank' => $user_rank
        ];
    }
}
