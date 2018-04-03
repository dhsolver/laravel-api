<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cms\CreateStopRequest;
use App\Tour;
use App\Http\Resources\StopResource;
use App\TourStop;
use App\Http\Resources\StopCollection;
use App\Http\Requests\Cms\UpdateStopRequest;
use App\Http\Requests\Cms\UploadStopMediaRequest;
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
        if ($tour->user_id != auth()->user()->id) {
            return response(null, 403);
        }

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

        return new StopResource(
            $tour->stops()->create(array_merge($request->validated(), ['order' => $order]))
        );
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
        if ($tour->user_id != auth()->user()->id) {
            return response(null, 403);
        }

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
        $stop->update($request->validated());

        return new StopResource(
            $stop->fresh()
        );
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
        if ($tour->user_id != auth()->user()->id) {
            return response(null, 403);
        }

        $stop->delete();

        return response(null, 204);
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

        $stop->save();

        return new StopCollection($tour->stops()->ordered()->get());
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

        return new StopResource($stop->fresh());
    }
}
