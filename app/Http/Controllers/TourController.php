<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourResource;
use App\Tour;
use App\Http\Requests\Cms\CreateTourRequest;
use App\Http\Requests\Cms\UpdateTourRequest;
use App\Http\Resources\TourCollection;

class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new TourCollection(
            auth()->user()->tours
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateTourRequest $request)
    {
        return new TourResource(
            auth()->user()->tours()->create($request->validated())
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Tour $tour)
    {
        if ($tour->user_id != auth()->user()->id) {
            return response(null, 403);
        }

        return new TourResource($tour);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTourRequest $request, Tour $tour)
    {
        $tour->update($request->validated());

        return new TourResource($tour);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tour $tour)
    {
        if ($tour->user_id != auth()->user()->id) {
            return response(null, 403);
        }

        $tour->delete();

        return response(null, 204);
    }

    public function uploadImages(Tour $tour)
    {
        if ($tour->user_id != auth()->user()->id) {
            return response(null, 403);
        }
    }
}
