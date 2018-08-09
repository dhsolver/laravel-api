<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

Route::post('auth/login/facebook', 'AuthController@facebook')->name('facebook.login');
Route::post('auth/login', 'AuthController@login');
Route::post('auth/signup', 'AuthController@signup');
Route::post('auth/forgot-password', 'ResetPasswordController@forgot');
Route::post('auth/reset-password', 'ResetPasswordController@reset');

Route::get('test', 'Controller@test');

Route::middleware(['jwt.auth'])->group(function () {
    Route::get('auth/session', 'AuthController@userSession');
});

Route::middleware(['jwt.refresh'])->group(function () {
    Route::get('auth/refresh', function () {
        return response(null, 204);
    });
});
