<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStopRequest;
use App\Tour;
use App\Http\Resources\StopResource;
use App\TourStop;
use App\Http\Resources\StopCollection;
use App\Http\Requests\UpdateStopRequest;
use App\Http\Requests\UploadStopMediaRequest;
use App\Http\Controllers\Traits\UploadsMedia;

class StopController extends Controller
{
    use UploadsMedia;

    /**
     * Lists all stops for a given tour.
     *
     * @param Tour $tour
     * @return StopCollection
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
     * @param Tour $tour
     * @return StopResource
     */
    public function store(CreateStopRequest $request, Tour $tour)
    {
        $order = $tour->getNextStopOrder();

        $data = array_merge($request->validated(), ['order' => $order]);

        if ($stop = $tour->stops()->create($data)) {
            return $this->success("The stop {$stop->title} was created successfully.", new StopResource(
                $stop->fresh()
            ));
        }

        return $this->fail();
    }

    /**
     * Gets the details of a given tour stop.
     *
     * @param Tour $tour
     * @param TourStop $stop
     * @return StopResource
     */
    public function show(Tour $tour, TourStop $stop)
    {
        return new StopResource($stop);
    }

    /**
     * Updates a tour stop with the given data.
     *
     * @param UpdateStopRequest $request
     * @param Tour $tour
     * @param TourStop $stop
     * @return StopResource
     */
    public function update(UpdateStopRequest $request, Tour $tour, TourStop $stop)
    {
        $stop->update(array_except($request->validated(), ['choices']));

        $stop->updateChoices($request->choices);

        return $this->success("{$stop->title} was updated successfully.", $stop->fresh());
    }

    /**
     * Deletes the given tour stop.
     *
     * @param Tour $tour
     * @param TourStop $stop
     * @return Response
     */
    public function destroy(Tour $tour, TourStop $stop)
    {
        if ($stop->delete()) {
            return $this->success("{$stop->title} was archived successfully.");
        }

        return $this->fail();
    }

    /**
     * Sets the order of the given tour stop.
     *
     * @param Tour $tour
     * @param TourStop $stop
     * @return StopCollection
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

    /**
     * Handles all media uploads.
     *
     * @param UploadStopMediaRequest $request
     * @param Tour $tour
     * @param TourStop $stop
     * @return StopResource
     */
    public function uploadMedia(UploadStopMediaRequest $request, Tour $tour, TourStop $stop)
    {
        // handle image uploads
        foreach (TourStop::$imageAttributes as $key) {
            if ($request->has($key)) {
                $filename = $this->storeFile($request->file($key), 'images');
                $stop->update([$key => $filename]);
            }
        }

        // handle audio uploads
        foreach (TourStop::$audioAttributes as $key) {
            if ($request->has($key)) {
                $filename = $this->storeFile($request->file($key), 'audio');
                $stop->update([$key => $filename]);
            }
        }

        return $this->success("{$stop->title} was updated successfully.", new StopResource($stop->fresh()));
    }
}
