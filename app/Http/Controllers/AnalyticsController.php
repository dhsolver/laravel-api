<?php

namespace App\Http\Controllers;

use App\Reports\DeviceDetailsReport;
use App\Reports\StopOverviewReport;
use App\Tour;
use Illuminate\Http\Request;
use App\Reports\TourDetailsReport;

class AnalyticsController extends Controller
{
    /**
     * Get the stop overview analytics report.
     *
     * @param Request $request
     * @param Tour $tour
     * @return \Illumiate\Http\Reponse
     */
    public function overview(Request $request, Tour $tour)
    {
        $report = new StopOverviewReport($tour);
        $data = $report->forDates($request->start, $request->end)
            ->run();

        return response()->json($data);
    }

    /**
     * Get the tour details analytics report.
     *
     * @param Request $request
     * @param Tour $tour
     * @return \Illumiate\Http\Response
     */
    public function details(Request $request, Tour $tour)
    {
        $report = new TourDetailsReport($tour);
        $data = $report->forDates($request->start, $request->end)
            ->run();

        return response()->json($data);
    }

    /**
     * Get the device details analytics report.
     *
     * @param Request $request
     * @param Tour $tour
     * @return \Illumiate\Http\Response
     */
    public function devices(Request $request, Tour $tour)
    {
        $report = new DeviceDetailsReport($tour);
        $data = $report->forDates($request->start, $request->end)
            ->run();

        return response()->json($data);
    }
}
