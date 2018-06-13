<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourResource;
use App\Tour;
use App\Http\Requests\CreateTourRequest;
use App\Http\Requests\UpdateTourRequest;
use App\Http\Resources\TourCollection;
use Illuminate\Support\Arr;

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
        $data = $request->validated();

        \DB::beginTransaction();

        if ($tour->update(Arr::except($data, ['location', 'route']))) {
            if ($request->has('location')) {
                $tour->location()->update($data['location']);
            }

            // dd($data['route']);
            if ($request->has('route')) {
                $tour->syncRoute($data['route']);
            }

            \DB::commit();

            $tour = $tour->fresh();
            return $this->success("{$tour->title} was updated successfully.", new TourResource($tour));
        }

        \DB::rollBack();
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
}
