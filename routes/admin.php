<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

// --------------------------------------------------------------------------
// GUEST ROUTES
Route::post('auth/login', 'Auth\AdminAuthController@login');

// --------------------------------------------------------------------------
// PROTECTED ROUTES
Route::middleware(['jwt.auth', 'role:superadmin|admin'])->group(function () {
    Route::get('auth/session', 'Auth\AdminAuthController@userSession');
});

Route::middleware(['jwt.refresh', 'role:superadmin|admin'])->group(function () {
    Route::get('auth/refresh', function () {
        return response(null, 204);
    });
});
