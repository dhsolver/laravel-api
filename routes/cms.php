<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

// --------------------------------------------------------------------------
// GUEST ROUTES
Route::post('auth/login', 'AuthController@login');
Route::post('auth/signup', 'Cms\CmsAuthController@signup');

// --------------------------------------------------------------------------
// PROTECTED ROUTES
Route::middleware(['jwt.auth', 'role:business'])->group(function () {
    Route::get('auth/session', 'AuthController@userSession');
});

Route::middleware(['jwt.refresh', 'role:business'])->group(function () {
    Route::get('auth/refresh', function () {
        return response(null, 204);
    });
});
