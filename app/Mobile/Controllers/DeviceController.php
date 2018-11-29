<?php

namespace App\Mobile\Controllers;

use App\Device;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceRequest;

class DeviceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDeviceRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDeviceRequest $request)
    {
        $data = $request->validated();

        if ($device = Device::findByUdid($data['device_udid'])) {
            auth()->user()->devices()->attach($device);
        } else {
            $device = auth()->user()->devices()->create($request->validated());
        }

        return response()->json([
            'device_id' => $device->id,
        ]);
    }
}
