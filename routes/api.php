<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

Route::post('auth/login', 'AuthController@login');
Route::post('auth/signup', 'AuthController@signup');

Route::middleware(['jwt.auth'])->group(function () {
    Route::get('auth/session', 'AuthController@userSession');
});

Route::middleware(['jwt.refresh'])->group(function () {
    Route::get('auth/refresh', function () {
        return response(null, 204);
    });
});
