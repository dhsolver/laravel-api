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
        $leaders = [];
        $i = 1;
        $collection = $this->collection->sortByDesc('points');
        $user_rank = 0;
        $current_user_id = auth()->user()->id;

        foreach ($collection as $leader) {
            // Sum top scores
            $scores = $leader->user->scoreCards()
                ->with('tour')
                ->onlyBest();
            $scoreCards = ScoreCardResource::collection($scores);
            $topScore = 0;
            for ($i = 0; $i < count($scoreCards); $i ++) {
                if ($i == 0) {
                    $topScore += $scoreCards[$i]->points;
                } else if ($scoreCards[$i-1]->tour_id != $scoreCards[$i]->tour_id) {
                    $topScore += $scoreCards[$i]->points;
                }
            }
            
            $user = new ProfileResource($leader->user);
            $leaders[] = [
                'points' => (int) $topScore,
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
