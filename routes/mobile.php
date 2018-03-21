<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

// --------------------------------------------------------------------------
// GUEST ROUTES
Route::post('auth/login', 'Auth\MobileAuthController@login');
Route::post('auth/signup', 'Auth\MobileAuthController@signup');

// --------------------------------------------------------------------------
// PROTECTED ROUTES
Route::middleware(['jwt.auth', 'role:user|business|admin'])->group(function () {
    Route::get('auth/session', 'Auth\MobileAuthController@userSession');
});

Route::middleware(['jwt.refresh', 'role:user|business|admin'])->group(function () {
    Route::get('auth/refresh', function () {
        return response(null, 204);
    });
});
