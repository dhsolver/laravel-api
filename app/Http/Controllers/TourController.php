<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourResource;
use App\Tour;
use App\Http\Requests\CreateTourRequest;
use App\Http\Requests\UpdateTourRequest;
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
        return TourResource::collection(
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
        if (auth()->user()->type->tours()->count() >= auth()->user()->tour_limit) {
            return $this->fail(422, 'You have reached your maximum number of allowed tours.');
        }

        if ($tour = auth()->user()->type->tours()->create($request->validated())) {
            return $this->success("The tour {$tour->title} was created successfully.", new TourResource(
                $tour->fresh()->load(['stops', 'route'])
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
        $tour->load(['stops', 'route']);

        return response()->json(new TourResource($tour));
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

        if ($tour->update(Arr::except($data, ['location', 'route', 'prize_location']))) {
            if ($request->has('location')) {
                $tour->location()->update(Arr::except($data['location'], ['id']));
            }

            if ($request->has('route')) {
                $tour->syncRoute($data['route']);
            }

            if ($request->has('prize_location')) {
                // var_dump($data);exit;
                $tour->prizeLocation()->update(Arr::except($data['prize_location'], ['id']));
            }

            $tour->fresh()->updateLength();

            \DB::commit();

            $tour = $tour->fresh()->load(['stops', 'route']);
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
            $order ++;
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
        $tour->load(['stops', 'route']);

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
            if (empty($tour->in_app_id)) {
                return $this->fail(422, 'Cannot publish tour without an In-App ID', new TourResource($tour));
            }

            if ($tour->isAwaitingApproval) {
                $tour->publishSubmissions()
                    ->pending()
                    ->first()
                    ->approve();
            } else {
                $submission = $tour->publishSubmissions()->create([
                    'tour_id' => $tour->id,
                    'user_id' => $tour->user_id,
                ]);
                $submission->approve();
            }

            $tour = $tour->fresh()->load(['stops', 'route']);
            return $this->success("{$tour->title} has been published.", new TourResource($tour));
        }

        if ($tour->submitForPublishing()) {
            $tour = $tour->fresh()->load(['stops', 'route']);
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
        $tour->load(['stops', 'route']);

        if ($tour->isAwaitingApproval) {
            $tour->publishSubmissions()->pending()->first()->delete();
            $tour = $tour->fresh()->load(['stops', 'route']);
            return $this->success("{$tour->title} has been removed from the approval queue.", new TourResource($tour));
        }

        $tour->published_at = null;
        $tour->save();

        return $this->success("{$tour->title} has been unpublished and removed from the apps.", new TourResource($tour));
    }
}
