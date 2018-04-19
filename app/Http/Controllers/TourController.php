<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourResource;
use App\Tour;
use App\Http\Requests\Cms\CreateTourRequest;
use App\Http\Requests\Cms\UpdateTourRequest;
use App\Http\Resources\TourCollection;
use App\Http\Controllers\Traits\UploadsMedia;
use App\Http\Requests\Cms\UploadTourMediaRequest;

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
            auth()->user()->type->tours
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
        if ($tour = auth()->user()->type->tours()->create($request->validated())) {
            return $this->success("The tour {$tour->title} was created successfully.", new TourResource(
                $tour->fresh()
            ));
        }

        return $this->fail();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Tour $tour)
    {
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
        if ($tour->update($request->validated())) {
            $tour = $tour->fresh();
            return $this->success("{$tour->title} was updated successfully.", new TourResource($tour));
        }

        return $this->fail();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tour $tour)
    {
        if ($tour->delete()) {
            return $this->success("{$tour->title} was archived successfully.");
        }
        return $this->fail();
    }

    /**
     * Handles all media uploads.
     *
     * @param UploadTourMediaRequest $request
     * @param Tour $tour
     * @return mixed
     */
    public function uploadMedia(UploadTourMediaRequest $request, Tour $tour)
    {
        // handle image uploads
        foreach (Tour::$imageAttributes as $key) {
            if ($request->has($key)) {
                $filename = $this->storeFile($request->file($key), 'images');
                $tour->update([$key => $filename]);
            }
        }

        // handle audio uploads
        foreach (Tour::$audioAttributes as $key) {
            if ($request->has($key)) {
                $filename = $this->storeFile($request->file($key), 'audio');
                $tour->update([$key => $filename]);
            }
        }

        return $this->success("{$tour->title} was updated successfully.", new TourResource($tour->fresh()));
    }
}
