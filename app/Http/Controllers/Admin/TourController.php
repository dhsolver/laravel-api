<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\TourResource;
use App\Tour;
use App\Http\Requests\CreateTourRequest;
use App\Http\Resources\TourCollection;
use App\Http\Controllers\TourController as BaseTourController;

class TourController extends BaseTourController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new TourCollection(Tour::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateTourRequest $request)
    {
        if ($tour = Tour::create($request->validated())) {
            return $this->success("The tour {$tour->name} was created successfully.", new TourResource(
                $tour->fresh()
            ));
        }

        return $this->fail();
    }
}
