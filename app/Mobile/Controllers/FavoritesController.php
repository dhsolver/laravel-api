<?php

namespace App\Mobile\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FavoritesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        request()->validate(['tour_id' => 'required|exists:tours,id']);

        auth()->user()->favorites()->attach(request()->tour_id);

        return response()->json(['favorites' => auth()->user()->fresh()->favorites->pluck('id')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        request()->validate(['tour_id' => 'required|exists:tours,id']);

        auth()->user()->favorites()->detach(request()->tour_id);

        return response()->json(['favorites' => auth()->user()->fresh()->favorites->pluck('id')]);
    }
}
