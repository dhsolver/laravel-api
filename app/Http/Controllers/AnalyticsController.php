<?php

namespace App\Http\Controllers;

use App\Reports\StopOverviewReport;
use App\Tour;
use Illuminate\Http\Request;
use App\Reports\TourDetailsReport;

class AnalyticsController extends Controller
{
    public function overview(Request $request, Tour $tour)
    {
        $report = new StopOverviewReport($tour);
        $data = $report->forDates($request->start, $request->end)
            ->run();

        return response()->json($data);
    }

    public function details(Request $request, Tour $tour)
    {
        $report = new TourDetailsReport($tour);
        $data = $report->forDates($request->start, $request->end)
            ->run();

        return response()->json($data);
    }

    public function devices(Request $request, Tour $tour)
    {
        return [];
    }
}
