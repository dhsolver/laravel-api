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
Route::post('confirm-email', 'ConfirmEmailController@confirm')->name('confirm-email');
// Route::get('test', 'Controller@test');

Route::middleware(['jwt.auth', 'active'])->group(function () {
    Route::get('auth/session', 'AuthController@userSession');
    Route::delete('auth/facebook', 'AuthController@facebookDetach')->name('facebook.detach');
    Route::post('auth/facebook/attach', 'AuthController@facebookAttach')->name('facebook.attach');
});

Route::middleware(['jwt.refresh'])->group(function () {
    Route::get('auth/refresh', function () {
        return response(null, 204);
    });
});
