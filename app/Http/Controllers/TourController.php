<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourResource;
use App\Tour;
use App\Http\Requests\Cms\CreateTourRequest;
use App\Http\Requests\Cms\UpdateTourRequest;
use App\Http\Resources\TourCollection;
use App\Http\Requests\Cms\UpdateTourImageRequest;
use App\Http\Controllers\Traits\UploadsMedia;

class TourController extends Controller
{
    use UploadsMedia;

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

    /**
     * Handles image uploads for all slots.
     *
     * @param UpdateTourImageRequest $request
     * @param Tour $tour
     * @return mixed
     */
    public function uploadImages(UpdateTourImageRequest $request, Tour $tour)
    {
        if ($request->has('main_image')) {
            $tour->main_image = $this->storeFile($request->file('main_image'), 'images');
        } elseif ($request->has('image_1')) {
            $tour->image_1 = $this->storeFile($request->file('image_1'), 'images');
        } elseif ($request->has('image_2')) {
            $tour->image_2 = $this->storeFile($request->file('image_2'), 'images');
        } elseif ($request->has('image_3')) {
            $tour->image_3 = $this->storeFile($request->file('image_3'), 'images');
        } else {
            return response('No images found.', 422);
        }

        $tour->save();

        return new TourResource($tour->fresh());
    }
}
