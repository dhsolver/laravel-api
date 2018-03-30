<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:business|admin|superadmin'])->group(function () {
    Route::get('session', 'AuthController@userSession');
    Route::resource('tours', 'TourController', ['as' => 'cms']);
});
