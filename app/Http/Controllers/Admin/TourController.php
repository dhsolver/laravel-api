<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\TourResource;
use App\Tour;
use App\Http\Requests\CreateTourRequest;
use App\Http\Controllers\TourController as BaseTourController;
use App\Http\Requests\Admin\TransferTourRequest;

class TourController extends BaseTourController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index()
    {
        return TourResource::collection(
            Tour::with('creator')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateTourRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateTourRequest $request)
    {
        $client = \App\Client::findOrFail($request->user_id);

        if ($client->tours()->count() >= $client->tour_limit) {
            return $this->fail(422, 'Tour limit reached.');
        }

        if ($tour = Tour::create($request->validated())) {
            return $this->success("The tour {$tour->name} was created successfully.", new TourResource(
                $tour->fresh()->load(['stops', 'route'])
            ));
        }

        return $this->fail();
    }

    /**
     * Transfer tour ownership to the requested user.
     *
     * @param TransferTourRequest $request
     * @param Tour $tour
     * @return \Illuminate\Http\Response
     */
    public function transfer(TransferTourRequest $request, Tour $tour)
    {
        $newClient = \App\Client::findOrFail($request->user_id);

        if ($newClient->tours_left == 0) {
            return $this->fail(422, 'Operation failed.  This would exceed the number of tours for the selected client.');
        }

        $tour->update(['user_id' => $request->user_id]);

        return $this->success('Tour was successfully transfered.');
    }
}
