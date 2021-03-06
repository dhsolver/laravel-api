<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Client;
use App\Http\Requests\Admin\CreateClientRequest;
use App\Http\Resources\ClientResource;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Http\Resources\UserDropdownResource;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index()
    {
        $clients = Client::withCount('tours')->get();

        if (request()->has('dropdown')) {
            return UserDropdownResource::collection($clients->sortBy('name'));
        }

        return ClientResource::collection($clients);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateClientRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateClientRequest $request)
    {
        $data = $request->validated();
        $data['user_type'] = 1;
        if ($client = Client::create($data)) {
            return $this->success("{$client->name} was added successfully.", new ClientResource($client));
        }

        return $this->fail();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Client $client
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(Client $client)
    {
        return new ClientResource($client);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateClientRequest $request
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        if ($client->update($request->validated())) {
            $client = $client->fresh();
            return $this->success("{$client->name} was updated successfully.", new ClientResource($client));
        }

        return $this->fail();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Client $client)
    {
        if ($client->delete()) {
            return $this->success("{$client->name} was archived successfully.");
        }

        return $this->fail();
    }
}
