<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStopRequest;
use App\Tour;
use App\Http\Resources\StopResource;
use App\TourStop;
use App\Http\Resources\StopCollection;
use App\Http\Requests\UpdateStopRequest;
use App\StopChoice;
use Illuminate\Support\Arr;

class StopController extends Controller
{
    /**
     * Lists all stops for a given tour.
     *
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index(Tour $tour)
    {
        return new StopCollection(
            $tour->stops
        );
    }

    /**
     * Stores a new stop for the given tour.
     *
     * @param CreateStopRequest $request
     * @param \App\Tour $tour
     * @return \Illuminate\Http\Response
     */
    public function store(CreateStopRequest $request, Tour $tour)
    {
        $order = $tour->getNextStopOrder();

        $data = array_merge($request->validated(), ['order' => $order]);

        \DB::beginTransaction();

        if ($stop = $tour->stops()->create(Arr::except($data, ['choices', 'location', 'routes']))) {
            if (isset($data['location'])) {
                $stop->location()->update(Arr::except($data['location'], ['id']));
            }

            if (isset($data['choices'])) {
                $stop->updateChoices($data['choices']);
            }

            if (isset($data['routes'])) {
                $stop->syncRoutes($data['routes']);
            }

            \DB::commit();

            return $this->success("The stop {$stop->title} was created successfully.", new StopResource(
                $stop->fresh()
            ));
        }

        \DB::rollback();
        return $this->fail();
    }

    /**
     * Gets the details of a given tour stop.
     *
     * @param \App\Tour $tour
     * @param \App\TourStop $stop
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(Tour $tour, TourStop $stop)
    {
        return new StopResource($stop);
    }

    /**
     * Updates a tour stop with the given data.
     *
     * @param UpdateStopRequest $request
     * @param \App\Tour $tour
     * @param \App\TourStop $stop
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStopRequest $request, Tour $tour, TourStop $stop)
    {
        $data = $request->validated();

        \DB::beginTransaction();

        if ($stop->update(Arr::except($data, ['choices', 'location', 'routes']))) {
            if (isset($data['location'])) {
                $stop->location()->update(Arr::except($data['location'], ['id']));
            }

            if (isset($data['choices'])) {
                $stop->updateChoices($data['choices']);
            }

            if (isset($data['routes'])) {
                $stop->syncRoutes($data['routes']);
            }

            \DB::commit();

            return $this->success("{$stop->title} was updated successfully.", new StopResource($stop->fresh()));
        }

        \DB::rollback();
        return $this->fail();
    }

    /**
     * Deletes the given tour stop.
     *
     * @param \App\Tour $tour
     * @param \App\TourStop $stop
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Tour $tour, TourStop $stop)
    {
        if (StopChoice::where('next_stop_id', $stop->id)->count() > 0) {
            return $this->fail(422, 'You cannot delete this stop because it is referenced in another stops destination points.');
        }

        if (TourStop::where('next_stop_id', $stop->id)->count() > 0) {
            return $this->fail(422, 'You cannot delete this stop because it is referenced in another stops destination points.');
        }

        if (Tour::where('start_point_id', $stop->id)->count() > 0) {
            return $this->fail(422, 'You cannot delete this stop because it set as the Tour\'s start point.');
        }

        if (Tour::where('end_point_id', $stop->id)->count() > 0) {
            return $this->fail(422, 'You cannot delete this stop because it set as the Tour\'s end point.');
        }

        $stop->choices()->delete();
        $stop->routes()->delete();

        if ($stop->delete()) {
            return $this->success("{$stop->title} was archived successfully.");
        }

        return $this->fail();
    }

    /**
     * Sets the order of the given tour stop.
     *
     * @param \App\Tour $tour
     * @param \App\TourStop $stop
     * @return \Illuminate\Http\Response
     */
    public function changeOrder(Tour $tour, TourStop $stop)
    {
        request()->validate([
            'order' => 'required|numeric'
        ]);

        $stop->order = abs(request()->order);

        $tour->increaseOrderAt($stop->order);

        if ($stop->save()) {
            return $this->success(
                "{$tour->title}'s stop order was updated successfully.",
                new StopCollection($tour->stops()->get())
            );
        }

        return $this->fail();
    }
}
