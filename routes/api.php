<?php


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// --------------------------------------------------------------------------
// GUEST ROUTES
Route::post('auth/login', 'AuthController@login');
Route::post('auth/signup', 'AuthController@signup');

// --------------------------------------------------------------------------
// PROTECTED ROUTES
Route::middleware(['jwt.auth'])->group(function () {
    Route::get('auth/session', 'AuthController@userSession');
});

Route::middleware(['jwt.refresh'])->group(function () {
    Route::get('auth/refresh', function () {
        return response(null, 204);
    });
});
