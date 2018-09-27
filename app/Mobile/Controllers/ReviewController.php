<?php

namespace App\Mobile\Controllers;

use App\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mobile\Requests\CreateReviewRequest;
use App\Tour;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Mobile\Resources\ReviewResource;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateReviewRequest $request, Tour $tour)
    {
        if (! $tour->isPublished) {
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
     * @param  \App\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy(Review $review)
    {
        if ($review->user_id != auth()->user()->id) {
            // not authorized
        }
    }
}
