<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourResource;
use App\Tour;
use App\Http\Requests\CreateTourRequest;
use App\Http\Requests\UpdateTourRequest;
use App\Http\Resources\TourCollection;
use Illuminate\Support\Arr;
use App\Http\Requests\UpdateStopOrderRequest;
use App\TourStop;

class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
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
     * @param \App\Http\Requests\CreateTourRequest $request
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
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Response
     */
    public function show(Tour $tour)
    {
        return json()->response(new TourResource($tour));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTourRequest $request
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTourRequest $request, Tour $tour)
    {
        $data = $request->validated();

        \DB::beginTransaction();

        if ($tour->update(Arr::except($data, ['location', 'route']))) {
            if ($request->has('location')) {
                $tour->location()->update(Arr::except($data['location'], ['id']));
            }

            if ($request->has('route')) {
                $tour->syncRoute($data['route']);
            }

            $tour->fresh()->updateLength();

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
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Tour $tour)
    {
        if ($tour->delete()) {
            return $this->success("{$tour->title} was archived successfully.");
        }

        return $this->fail();
    }

    /**
     * Updates the order of all Tour's stops.
     *
     * @param UpdateStopOrderRequest $request
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Response
     */
    public function stopOrder(UpdateStopOrderRequest $request, Tour $tour)
    {
        $order = 1;
        foreach ($request->order as $key => $id) {
            TourStop::where('id', $id)->update(['order' => $order]);
            $order++;
        }

        return $this->success('Stop order successfully saved.', ['order' => $request->order]);
    }

    /**
     * Submit tour for publish approval.
     *
     * @param Tour $tour
     * @return \Illuminate\Http\Response
     */
    public function publish(Tour $tour)
    {
        if ($errors = $tour->audit()) {
            return $this->fail(422, 'Cannot publish tour.', [
                'tour' => new TourResource($tour),
                'errors' => $errors,
            ]);
        }

        if ($tour->isPublished) {
            return $this->fail(422, "{$tour->title} has already been published.", new TourResource($tour));
        }

        // auto-approve tour for admins
        if (auth()->user()->isAdmin()) {
            if ($tour->isAwaitingApproval) {
                $tour->publishSubmissions()
                    ->pending()
                    ->approve();
            } else {
                $submission = $tour->publishSubmissions()->create([
                    'tour_id' => $tour->id,
                    'user_id' => $tour->user_id,
                ]);
                $submission->approve();
            }

            $tour = $tour->fresh();
            return $this->success("{$tour->title} has been published.", new TourResource($tour));
        }

        if ($tour->submitForPublishing()) {
            $tour = $tour->fresh();
            return $this->success("{$tour->title} has been submitted for publishing and awaiting approval.", new TourResource($tour));
        }

        return $this->fail();
    }

    /**
     * Un-publish the tour, or cancel a publish request.
     *
     * @param Tour $tour
     * @return \Illuminate\Http\Response
     */
    public function unpublish(Tour $tour)
    {
        if ($tour->isAwaitingApproval) {
            $tour->publishSubmissions()->pending()->first()->delete();
            $tour = $tour->fresh();
            return $this->success("{$tour->title} has been removed from the approval queue.", new TourResource($tour));
        }

        $tour->published_at = null;
        $tour->save();

        return $this->success("{$tour->title} has been unpublished and removed from the apps.", new TourResource($tour));
    }
}
