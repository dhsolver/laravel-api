<?php

namespace App\Mobile\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Mobile\Requests\CreateReviewRequest;
use App\Mobile\Resources\ReviewResource;
use App\Http\Controllers\Controller;
use App\Tour;

class ReviewController extends Controller
{
    /**
     * Display a paginated list of the Tours reviews.
     *
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index(Tour $tour)
    {
        return ReviewResource::collection(
            $tour->reviews()->latest()->paginate()
        );
    }

    /**
     * Upsert a review for the Tour.
     *
     * @param CreateReviewRequest $request
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Response
     * @throws ModelNotFoundException
     */
    public function store(CreateReviewRequest $request, Tour $tour)
    {
        if (! $tour->isPublished && $request->debug != 1) {
            throw new ModelNotFoundException('Tour not found.');
        }

        $data = array_merge($request->validated(), ['user_id' => auth()->user()->id]);

        if ($review = $tour->reviews()->byUser(auth()->user()->id)->first()) {
            $review->update($data);
        } else {
            $review = $tour->reviews()->create($data);
        }

        return response()->json(new ReviewResource($review->fresh()));
    }

    /**
     * Remove the user's review.
     *
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tour $tour)
    {
        if ($review = $tour->reviews()->byUser(auth()->user()->id)->first()) {
            $review->delete();
        }

        return $this->success('Your review has been removed.');
    }
}
